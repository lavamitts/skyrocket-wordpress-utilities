(function () {
  const cfg = window.bbcLiveSearch;
  console.log("running live search extension");
  if (!cfg) return;

  const cache = new Map();
  let token = 0;

  const decode = (html) => new DOMParser().parseFromString(html, "text/html").documentElement.textContent;

  function fetchGroup(term, groupConfig) {
    const url = new URL(groupConfig.endpoint, window.location.origin);
    url.searchParams.set("search", term);
    url.searchParams.set("per_page", cfg.perType);
    url.searchParams.set("strict_type", groupConfig.postType);
    url.searchParams.set("_cb", Date.now()); // Bypass REST caching layers

    return fetch(url).then((r) => (r.ok ? r.json() : []));
  }

  function fetchAll(term) {
    if (!cache.has(term)) {
      cache.set(term, Promise.all(cfg.groups.map((g) => fetchGroup(term, g))));
    }
    return cache.get(term);
  }

  function getTerm(container) {
    const form = container.closest("form");
    const input = (form && form.querySelector('input[name="s"]')) || document.querySelector('#search-modal input[name="s"]');
    return input ? input.value.trim() : "";
  }

  function group(container) {
    if (container.querySelector(".bbc-ls-columns")) return;
    const term = getTerm(container);
    if (!term) return;

    const myToken = ++token;
    fetchAll(term)
      .then((results) => {
        if (myToken !== token || container.querySelector(".bbc-ls-columns")) return;
        if (!container.isConnected || getTerm(container) !== term) return;

        const more = container.querySelector(".ct-search-more");
        const wrap = document.createElement("div");
        wrap.className = "bbc-ls-columns";

        cfg.groups.forEach((g, i) => {
          const items = results[i] || [];
          if (!items.length) return;

          const col = document.createElement("div");
          col.className = "bbc-ls-column";

          const h = document.createElement("div");
          h.className = "bbc-ls-heading";
          h.textContent = g.label;
          col.appendChild(h);

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

        if (!wrap.children.length) return;
        wrap.style.setProperty("--bbc-cols", wrap.children.length);

        container.replaceChildren(wrap);
        if (more) container.appendChild(more);
      })
      .catch(() => {});
  }

  const observer = new MutationObserver(() => {
    document.querySelectorAll(".ct-search-results").forEach(group);
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();
