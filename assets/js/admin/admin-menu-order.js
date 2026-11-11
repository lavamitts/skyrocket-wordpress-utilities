jQuery(function ($) {
  const menuList = $("#swu-admin-menu-sortable");

  if (!menuList.length) {
    return;
  }

  menuList.sortable({
    axis: "y",
    handle: ".swu-menu-order-handle",
    items: "> li",
    placeholder: "swu-menu-order-placeholder",
    forcePlaceholderSize: true,
    tolerance: "pointer",
  });
});
