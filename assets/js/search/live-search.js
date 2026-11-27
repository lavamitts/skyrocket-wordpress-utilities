(function () {
  console.log("Using the new file 2");
  const cfg = window.bbcLiveSearch;
  if (!cfg) return;

  // Disable Blocksy live-search thumbnails.
  // Keep live search enabled.
  document.querySelectorAll("form[data-live-results]").forEach((form) => {
    const settings = form.dataset.liveResults || "";

    if (settings.includes("thumbs")) {
      form.dataset.liveResults = settings.replace("thumbs", "").trim() || "true";
    }
  });

  const cache = new Map();
  const states = new WeakMap();

  const decode = (html) => new DOMParser().parseFromString(html, "text/html").documentElement.textContent;

  function fetchGroup(term, groupConfig) {
    const url = new URL(groupConfig.endpoint, window.location.origin);

    url.searchParams.set("search", term);
    url.searchParams.set("per_page", cfg.perType);
    url.searchParams.set("strict_type", groupConfig.postType);
    url.searchParams.set("_cb", Date.now());

    return fetch(url).then((r) => {
      if (!r.ok) throw new Error("Search request failed");
      return r.json();
    });
  }

  function fetchAll(term) {
    if (!cache.has(term)) {
      const request = Promise.all(cfg.groups.map((g) => fetchGroup(term, g))).catch((error) => {
        cache.delete(term);
        throw error;
      });

      cache.set(term, request);
    }

    return cache.get(term);
  }

  function getTerm(container) {
    const form = container.closest("form");
    const input = form?.querySelector('input[name="s"]');

    return input ? input.value.trim() : "";
  }

  function group(container) {
    if (container.querySelector(".bbc-ls-columns")) return;

    const term = getTerm(container);
    if (!term) return;

    let state = states.get(container);

    // Don't start another request for the same search term.
    if (state?.term === term && state.pending) return;

    if (!state) {
      state = { term: "", pending: false };
      states.set(container, state);
    }

    state.term = term;
    state.pending = true;

    // Hide Blocksy's default results immediately.
    container.classList.add("bbc-ls-loading");

    fetchAll(term)
      .then((results) => {
        if (!container.isConnected) return;
        if (state.term !== term) return;
        if (getTerm(container) !== term) return;

        const more = container.querySelector(".ct-search-more");
        const wrap = document.createElement("div");

        wrap.className = "bbc-ls-columns";

        cfg.groups.forEach((g, i) => {
          const items = results[i] || [];
          if (!items.length) return;

          const col = document.createElement("div");
          col.className = "bbc-ls-column";

          const heading = document.createElement("div");
          heading.className = "bbc-ls-heading";
          heading.textContent = g.label;

          col.appendChild(heading);

          items.forEach((item) => {
            const a = document.createElement("a");

            a.className = "ct-search-item";
            a.setAttribute("role", "option");
            a.href = item.link || item.url;

            const span = document.createElement("span");
            const rawTitle = typeof item.title === "object" ? item.title.rendered : item.title;

            span.textContent = decode(rawTitle || "");

            a.appendChild(span);
            col.appendChild(a);
          });

          wrap.appendChild(col);
        });

        // Only replace Blocksy's results if we have custom results.
        if (!wrap.children.length) return;

        wrap.style.setProperty("--bbc-cols", wrap.children.length);

        container.replaceChildren(wrap);

        if (more) container.appendChild(more);

        // Reveal the completed custom layout.
        container.classList.remove("bbc-ls-loading");
      })
      .catch(() => {
        // If our custom search fails, retain Blocksy's results.
        if (container.isConnected && state.term === term) {
          container.classList.remove("bbc-ls-loading");
        }
      })
      .finally(() => {
        if (state.term === term) {
          state.pending = false;
        }
      });
  }

  const observer = new MutationObserver((records) => {
    records.forEach((record) => {
      const target = record.target;

      if (target.nodeType === 1) {
        const container = target.matches(".ct-search-results") ? target : target.closest(".ct-search-results");

        if (container) group(container);
      }

      record.addedNodes.forEach((node) => {
        if (node.nodeType !== 1) return;

        if (node.matches(".ct-search-results")) {
          group(node);
        }

        node.querySelectorAll(".ct-search-results").forEach(group);
      });
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });

  // Handle results already present when this script loads.
  document.querySelectorAll(".ct-search-results").forEach(group);
})();
