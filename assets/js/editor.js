(function () {

    function set_shortcodes_atts (editor, atts) {
        var titreFenetre = !_.isUndefined(atts.nom) ? atts.nom : 'Ajouter un shortcode';
        var balise = !_.isUndefined(atts.balise) ? atts.balise : false;

        fn = function () {
            editor.windowManager.open({
                title: titreFenetre,
                body: atts.body,
                onsubmit: function (e) {
                    var out = '[' + balise;
                    for (var attr in e.data) {
                        out += ' ' + attr + '="' + e.data[attr] + '"';
                    }
                    out += ']';
                    editor.insertContent(out);
                },
            });
        };
        return fn;
    }

    tinymce.PluginManager.add('_pip_shortcodes', function (editor, url) {
        editor.addButton('_pip_shortcodes_button', {
            icon: false,
            text: 'Shortcodes',
            type: 'listbox',
            values: [
                {
                    text: 'Bouton',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Texte',
                                name: 'text',
                                type: 'textbox',
                                value: '',
                            },
                            {
                                label: 'Lien',
                                name: 'link',
                                type: 'textbox',
                                value: '',
                            },
                            {
                                label: 'Ouverture',
                                name: 'opening',
                                type: 'listbox',
                                values: [
                                    { text: 'Ouvrir dans la page', value: '' },
                                    { text: 'Ouvrir dans un nouvel onglet', value: '_target' },
                                ],
                            },
                            {
                                label: 'Taille',
                                name: 'size',
                                type: 'listbox',
                                values: [
                                    { text: 'Normal', value: '' },
                                    { text: 'Petit', value: 'btn-sm' },
                                    { text: 'Moyen', value: 'btn-md' },
                                    { text: 'Grand', value: 'btn-lg' },
                                ],
                            },
                            {
                                label: 'Type couleur',
                                name: 'color_type',
                                type: 'listbox',
                                values: [
                                    { text: 'Simple', value: '' },
                                    { text: 'Dégradé', value: 'gradient' },
                                ],
                            },
                            {
                                label: 'Bords',
                                name: 'border',
                                type: 'listbox',
                                values: [
                                    { text: 'Carré', value: 'square' },
                                    { text: 'Un peu arrondi', value: 'rounded' },
                                    { text: 'Arrondi', value: 'round' },
                                ],
                            },
                            {
                                label: 'Style',
                                name: 'style',
                                type: 'listbox',
                                values: [
                                    { text: 'Fond uni', value: 'plain' },
                                    { text: 'Bords colorés', value: 'outline' },
                                ],
                            },
                            {
                                label: 'Couleur',
                                name: 'color',
                                type: 'listbox',
                                values: [
                                    { text: 'Couleur primaire', value: 'primary' },
                                    { text: 'Couleur secondaire', value: 'secondary' },
                                    { text: 'Couleur blanche', value: 'white' },
                                    { text: 'Couleur grisé', value: 'grey' },
                                    { text: 'Couleur info', value: 'info' },
                                    { text: 'Couleur succès', value: 'success' },
                                    { text: 'Couleur danger', value: 'danger' },
                                    { text: 'Couleur attention', value: 'warning' },
                                ],
                            },
                            {
                                label: 'Style CSS',
                                name: 'css',
                                type: 'textbox',
                                value: '',
                            },
                            {
                                label: 'Ciblage - Catégorie',
                                name: 'c_cat',
                                type: 'listbox',
                                values: [
                                    { text: 'Macro Conversion', value: 'macro-conversion' },
                                    { text: 'Micro Conversion', value: 'micro-conversion' },
                                    { text: 'Action Utilisateur', value: 'user-event' },
                                ],
                            },
                            {
                                label: 'Ciblage - Action',
                                name: 'c_action',
                                type: 'textbox',
                                value: '',
                            },
                            {
                                label: 'Ciblage - Label',
                                name: 'c_label',
                                type: 'textbox',
                                value: '',
                            },
                        ],
                        balise: 'button',
                        nom: 'Ajouter un bouton',
                    }),
                },
                {
                    text: 'Image à la une',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Taille',
                                name: 's',
                                type: 'listbox',
                                values: [
                                    /** image size */
                                    { text: 'Très petite', value: 'thumbnail' },
                                    { text: 'Petite', value: 'medium' },
                                    { text: 'Moyenne', value: 'medium_large' },
                                    { text: 'Grande', value: 'large' },
                                    { text: 'Taille réelle', value: 'full' },
                                ],
                            },
                        ],
                        balise: 'thumbnail',
                        nom: 'Ajouter l\'image à la une',
                    }),
                },
                {
                    text: 'Icône',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Icone (ex: fa-box)',
                                name: 'i',
                                type: 'textbox',
                                value: 'fa-',
                            },
                            {
                                label: 'Style',
                                name: 's',
                                type: 'listbox',
                                values: [
                                    /** add fa style */
                                    { text: 'Solid', value: 'fas' },
                                    { text: 'Regular', value: 'far' },
                                    { text: 'Light', value: 'fal' },
                                    { text: 'Brands', value: 'fab' },
                                ],
                            },
                        ],
                        balise: 'icon',
                        nom: 'Ajouter un icône',
                    }),
                },
                {
                    text: 'Titre',
                    onclick: function () {
                        editor.insertContent('[titre]');
                    }
                },
                {
                    text: 'Catégorie',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Taxonomie',
                                name: 'taxonomy',
                                type: 'textbox',
                                value: 'category',
                            },
                            {
                                label: 'En lien avec ce contenu ?',
                                name: 'rel',
                                type: 'listbox',
                                values: [
                                    { text: 'Oui', value: '1' },
                                    { text: 'Non', value: '0' },
                                ],
                            },
                            {
                                label: 'Une seule ou plusieurs ?',
                                name: 'one',
                                type: 'listbox',
                                values: [
                                    { text: 'Une seule', value: '1' },
                                    { text: 'Plusieurs', value: '0' },
                                ],
                            },
                        ],
                        balise: 'terms',
                        nom: 'Ajouter une catégorie',
                    }),
                },
                {
                    text: 'Terms',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Taxonomie',
                                name: 'taxonomy',
                                type: 'textbox',
                                value: '',
                            },
                            {
                                label: 'En lien avec ce contenu ?',
                                name: 'rel',
                                type: 'listbox',
                                values: [
                                    { text: 'Oui', value: '1' },
                                    { text: 'Non', value: '0' },
                                ],
                            },
                            {
                                label: 'Un seul ou plusieurs ?',
                                name: 'one',
                                type: 'listbox',
                                values: [
                                    { text: 'Un seul', value: '1' },
                                    { text: 'Plusieurs', value: '0' },
                                ],
                            },
                        ],
                        balise: 'terms',
                        nom: 'Ajouter des terms',
                    }),
                },
                {
                    text: 'Date',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Type',
                                name: 'type',
                                type: 'listbox',
                                values: [
                                    { text: 'Date de publication', value: 'publi' },
                                    { text: 'Date de modification', value: 'modif' },
                                ],
                            },
                            {
                                label: 'Format',
                                name: 'format',
                                type: 'textbox',
                                value: 'j F Y',
                            }
                        ],
                        balise: 'date',
                        nom: 'Ajouter une date',
                    }),
                },
                {
                    text: 'Information',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Type',
                                name: 'type',
                                type: 'listbox',
                                values: [
                                    { text: 'name', value: 'name' },
                                    { text: 'description', value: 'description' },
                                    { text: 'wpurl', value: 'wpurl' },
                                    { text: 'url', value: 'url' },
                                    { text: 'admin_email', value: 'admin_email' },
                                    { text: 'charset', value: 'charset' },
                                    { text: 'version', value: 'version' },
                                    { text: 'html_type', value: 'html_type' },
                                    { text: 'text_direction', value: 'text_direction' },
                                    { text: 'language', value: 'language' },
                                    { text: 'stylesheet_url', value: 'stylesheet_url' },
                                    { text: 'stylesheet_directory', value: 'stylesheet_directory' },
                                    { text: 'template_url', value: 'template_url' },
                                    { text: 'template_directory', value: 'template_directory' },
                                    { text: 'pingback_url', value: 'pingback_url' },
                                    { text: 'atom_url', value: 'atom_url' },
                                    { text: 'rdf_url', value: 'rdf_url' },
                                    { text: 'rss_url', value: 'rss_url' },
                                    { text: 'rss2_url', value: 'rss2_url' },
                                    { text: 'comments_atom_url', value: 'comments_atom_url' },
                                    { text: 'comments_rss2_url', value: 'comments_rss2_url' },
                                ]
                            },
                        ],
                        balise: 'bloginfo',
                        nom: 'Information',
                    }),
                },
                {
                    text: 'Menu',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Menu',
                                name: 'menu',
                                type: 'textbox',
                                value: '',
                            }
                        ],
                        balise: 'menu',
                        nom: 'Ajouter un menu',
                    }),
                },
                {
                    text: 'Option',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'Type',
                                name: 'type',
                                type: 'textbox',
                                value: '',
                            }
                        ],
                        balise: 'option',
                        nom: 'Ajouter une option',
                    }),
                },
                {
                    text: 'Partager',
                    onclick: function () {
                        editor.insertContent('[share via="facebook, twitter, linkedin, google-plus, mail, impression"]');
                    }
                },
                /**
                 *  Yoast SEO
                 */
                {
                    text: 'Fil d\'ariane',
                    onclick: function () {
                        editor.insertContent('[breadcrumb]');
                    }
                },
                /**
                 *  WooCommerce
                 */
                {
                    text: 'Ajouter au panier',
                    onclick: set_shortcodes_atts(editor, {
                        body: [
                            {
                                label: 'ID Produit',
                                name: 'id',
                                type: 'textbox',
                                value: '',
                            },
                            {
                                label: 'Afficher le prix ?',
                                name: 'show_price',
                                type: 'listbox',
                                values: [
                                    { text: 'Oui', value: 'true' },
                                    { text: 'Non', value: 'false' },
                                ],
                            },
                            {
                                label: 'Quantité',
                                name: 'quantity',
                                type: 'textbox',
                                value: '1',
                            },
                        ],
                        balise: 'add_to_cart',
                        nom: 'Ajouter au panier',
                    }),
                },
            ]
        });

    });

})();
