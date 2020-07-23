(function () {
  tinymce.create("tinymce.plugins.md_flipbox", {
    /**
     * Initializes the plugin, this will be executed after the plugin has been created.
     * This call is done before the editor instance has finished it's initialization so use the onInit event
     * of the editor instance to intercept that event.
     *
     * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
     * @param {string} url Absolute URL to where the plugin is located.
     */
    init: function (ed, url) {
      // Left-Aligned Pullquote
      ed.addCommand("md_flipbox", function () {
        var selected_text = ed.selection.getContent();
        var return_text = "";
        return_text =
          '[md_flipbox title="Flipbox" fa_icon="fas fa-shopping-cart" sub_title="" url="" text="Lorem ipsum dolar sit amet lorem ipsum dolar sit amet lorem ipsum dolar sit amet"]' +
          selected_text.replace(/<\/?p[^>]*>/g, " ") +
          "[/md_flipbox]<br/><br/>";
        ed.execCommand("mceInsertContent", 0, return_text);
      });

      // Pullquote Menu Button http://www.tinymce.com/wiki.php/api4:class.tinymce.ui.MenuButton
      ed.addButton("md_flipbox-menu", {
        border: "1 1 1 1",
        // text: "Boxinfo",
        text: "Flipbox",
        tooltip: "Insert flipbox shortcode",
        //icon: true,
        icon: " fa fa-info-circle",
        //fa fa-info-circle
        //image: url + "./../images/quote-left.png",
        size: "small",
        onclick: function () {
          ed.execCommand("md_flipbox");
          return;

          ed.windowManager.open({
            title: "Container",
            body: [
              {
                type: "listbox",
                name: "style",
                label: "Style",
                values: [
                  { text: "Clear", value: "clear" },
                  { text: "White", value: "white" },
                  { text: "Colour 1", value: "colour1" },
                  { text: "Colour 2", value: "colour2" },
                  { text: "Colour 3", value: "colour3" },
                ],
              } /*add in the following with the comma */,
              { type: "container", html: "<p>Enter your Text here</p>" },
            ],
            onsubmit: function (e) {
              ed.insertContent(
                '[md_flipbox title="Flipbox"]' + "[/md_flipbox]<br/><br/>"
              );
            },
          });
        },
      });
    },

    /**
     * Creates control instances based in the incomming name. This method is normally not
     * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
     * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
     * method can be used to create those.
     *
     * @param {String} n Name of the control to create.
     * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
     * @return {tinymce.ui.Control} New control instance or null if no control was created.
     */
    createControl: function (n, cm) {
      return null;
    },

    /**
     * Returns information about the plugin as a name/value array.
     * The current keys are longname, author, authorurl, infourl and version.
     *
     * @return {Object} Name/value array containing information about the plugin.
     */
    getInfo: function () {
      return {
        longname: "md_flipbox",
        author: "Revious",
        authorurl: "",
        infourl: "",
        version: "1.7.5",
      };
    },
  });

  // Register plugin
  tinymce.PluginManager.add("md_flipbox", tinymce.plugins.md_flipbox);
})();
