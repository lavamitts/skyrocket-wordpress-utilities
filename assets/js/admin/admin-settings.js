jQuery(function ($) {
  let mediaFrame;

  // Open the WordPress media library.
  $(".swu-select-image").on("click", function (event) {
    event.preventDefault();

    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title: "Select login logo",
      button: {
        text: "Use this image",
      },
      library: {
        type: "image",
      },
      multiple: false,
    });

    // When an image is selected.
    mediaFrame.on("select", function () {
      const attachment = mediaFrame.state().get("selection").first().toJSON();

      const imageUrl = attachment.url;

      $("#swu-login-logo-id").val(attachment.id);

      $(".swu-image-preview").html(
        $("<img>", {
          src: imageUrl,
          alt: "",
          css: {
            maxWidth: "200px",
            maxHeight: "100px",
          },
        }),
      );

      $(".swu-remove-image").prop("disabled", false);
    });

    mediaFrame.open();
  });

  // Remove the currently selected image.
  $(".swu-remove-image").on("click", function (event) {
    event.preventDefault();

    $("#swu-login-logo-id").val("");

    $(".swu-image-preview").text("No logo selected");

    $(this).prop("disabled", true);
  });
});
