declare var tinymce: any;

tinymce.create("tinymce.plugins.md_youtube", {
    init: function (ed, url) {
        ed.addCommand("md_youtube", () => {
            const selected_text = ed.selection.getContent();
            let return_text = "";
            return_text =
                    '[md_youtube url=""]' +
                    selected_text.replace(/<\/?p[^>]*>/g, " ") +
                    "[/md_youtube]<br/><br/>";
            ed.execCommand("mceInsertContent", 0, return_text);
        });

        ed.addButton("md_youtube-menu", {
            border: "1 1 1 1",
            text: "Youtube",
            tooltip: "Insert youtube shortcode",
            icon: " fa fa-youtube-play",
            size: "small",
            onclick: () => {
                ed.execCommand("md_youtube");
                return;
            },
        });
    },

    createControl: function (n, cm) {
        return null;
    },

    getInfo: function () {
        return {
            longname: "md_youtube",
            author: "Revious",
            authorurl: "",
            infourl: "",
            version: "1.7.5",
        };
    },
});

tinymce.PluginManager.add("md_youtube", tinymce.plugins.md_youtube);
