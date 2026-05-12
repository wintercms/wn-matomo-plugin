<?php return [
    'plugin' => [
        'name' => 'Matomo Analytics',
        'description' => 'Intègre Matomo Analytics avec Winter CMS',
    ],

    'permissions' => [
        'site' => [
            'view' => 'Consulter les données analytiques des projets accessibles',
        ],
    ],

    'components' => [
        'tracker' => [
            'name' => 'Tracker',
            'description' => 'Inclut le code de suivi Matomo dans le thème. Doit être placé avant la balise fermante </head>',
        ],
    ],

    'reportwidgets' => [
        'general' => [
            'period'             => 'Période',
            'period_desc'        => 'La période de reporting',
            'period_last_x_days' => ':x derniers jours',
            'refresh'            => 'Actualiser',
            'calendar' => [
                'monthNames' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                'monthNamesShort' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                'weekdays' => ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                'weekdaysShort' => ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            ],
            'groups'             => [
                'metrics' => 'Indicateurs à afficher',
                'display' => 'Options d\'affichage',
            ],
            'show_refresh_button' => 'Afficher le bouton d\'actualisation',
            'show_widget_meta'    => 'Afficher les métadonnées du widget'
        ],

        'visits_over_time' => [
            'label'        => 'Visites dans le temps',
            'title_default' => 'Visites dans le temps',
            'days'         => 'Période',
            'days_desc'    => 'Nombre de jours passés à afficher sur le graphique.',
            'days_options' => [
                '7'  => '7 derniers jours',
                '14' => '14 derniers jours',
                '30' => '30 derniers jours',
                '90' => '90 derniers jours',
            ],
            'days_label'   => 'Période',
            'total_visits'    => 'Total des visites',
            'total_hits'      => 'Total des hits',
            'total_pageviews' => 'Total des vues de page',
            'no_data'         => 'Aucune donnée de visite disponible pour cette période.',
            'metrics' => [
                'nb_visits'    => 'Visites',
                'nb_actions'   => 'Hits',
                'nb_pageviews' => 'Vues de page',
            ],
            'chart' => [
                'tooltip_content' => '%s | %x | %y',
            ],
        ],

        'embedded_dashboard' => [
            'title_default' => 'Tableau de bord Analytics',
        ],

        'errors' => [
            'configuration'      => 'La configuration Matomo est invalide. Vérifiez MATOMO_SERVER, MATOMO_SITE_ID et MATOMO_TOKEN.',
            'authentication'     => "L'authentification Matomo a échoué. Vérifiez les permissions et la valeur du token.",
            'timeout'            => 'Le serveur Matomo est inaccessible ou trop lent. Réessayez dans un moment.',
            'host_unreachable'   => 'L\'hôte Matomo ":host" ne peut pas être résolu. Vérifiez MATOMO_SERVER.',
            'connection_refused' => 'La connexion à l\'hôte Matomo ":host" a été refusée. Vérifiez la disponibilité du serveur et le port.',
            'ssl_certificate'    => 'La vérification du certificat SSL a échoué pour le serveur Matomo. Vérifiez le certificat du serveur ou mettez à jour les certificats CA du système.',
            'dns_resolution'     => 'L\'hôte Matomo ":host" ne peut pas être résolu. Vérifiez la configuration DNS et la connectivité réseau.',
            'connection_failed'  => 'La connexion au serveur Matomo a échoué. Vérifiez la disponibilité du serveur et la configuration réseau.',
            'server'             => 'Le serveur Matomo a renvoyé une réponse inattendue. Vérifiez la disponibilité du serveur et les logs.',
            'reporting'          => 'Matomo a renvoyé une erreur analytique pour cette requête. Vérifiez les paramètres du widget et les logs.',
            'unexpected'         => 'Une erreur inattendue est survenue lors du chargement des données analytiques. Consultez les logs pour plus de détails.',
        ],

        'visits_summary' => [
            'label'       => 'Résumé des visites',
            'title_default' => 'Résumé des visites',
            'period'      => 'Période',
            'period_desc' => 'Sélectionnez la période de reporting pour le résumé.',
            'period_options' => [
                'day'   => 'Jour',
                'week'  => 'Semaine',
                'month' => 'Mois',
                'year'  => 'Année',
            ],
            'date'      => 'Date',
            'date_desc' => 'Sélectionnez la plage de dates pour le résumé.',
            'date_options' => [
                'today'     => "Aujourd'hui",
                'yesterday' => 'Hier',
                'last7'     => '7 derniers jours',
                'last30'    => '30 derniers jours',
                'last90'    => '90 derniers jours',
            ],
            'selected_period' => 'Période sélectionnée',
            'selected_date'   => 'Date sélectionnée',
            'metrics' => [
                'nb_visits'           => 'Visites',
                'nb_uniq_visitors'    => 'Visiteurs uniques',
                'nb_actions'          => 'Actions',
                'bounce_rate'         => 'Taux de rebond',
                'nb_actions_per_visit' => 'Actions par visite',
                'avg_time_on_site'    => 'Durée moyenne sur le site',
            ],
        ],

        'top_pages' => [
            'label' => 'Pages populaires',
            'title_default' => 'Pages populaires',
            'period' => 'Période',
            'period_desc' => 'Sélectionnez la période de reporting pour les pages populaires.',
            'period_options' => [
                'day' => 'Jour',
                'week' => 'Semaine',
                'month' => 'Mois',
                'year' => 'Année',
            ],
            'date' => 'Date',
            'date_desc' => 'Sélectionnez la plage de dates pour les pages populaires.',
            'date_options' => [
                'today' => 'Aujourd\'hui',
                'yesterday' => 'Hier',
                'last7' => '7 derniers jours',
                'last30' => '30 derniers jours',
                'last90' => '90 derniers jours',
            ],
            'limit' => 'Limite',
            'limit_desc' => 'Nombre maximum de pages à afficher.',
            'limit_options' => [
                5 => '5 pages',
                10 => '10 pages',
                20 => '20 pages',
            ],
            'selected_period' => 'Période sélectionnée',
            'selected_date' => 'Date sélectionnée',
            'selected_limit' => 'Limite',
            'selected_view_mode' => 'Mode d’affichage',
            'view_mode' => 'Mode d’affichage',
            'view_mode_desc' => 'Choisissez le mode d’affichage : liste à plat ou arbre hiérarchique.',
            'view_mode_options' => [
                'flat' => 'À plat',
                'hierarchical' => 'Hiérarchique',
            ],
            'exclude_low_pop' => 'Exclure les pages peu visitées',
            'exclude_low_pop_desc' => 'Exclure les pages ayant moins de visites que le seuil ci-dessous.',
            'exclude_low_pop_value' => 'Seuil minimum de visites',
            'exclude_low_pop_value_desc' => 'Les pages avec moins de visites que cette valeur seront exclues.',
            'exclude_low_pop_value_validation' => 'Le seuil minimum de visites doit être un entier positif.',
            'no_data' => 'Aucune donnée de page disponible pour cette période.',
            'columns' => [
                'url' => 'URL de la page',
                'nb_visits' => 'Visites',
                'bounce_rate' => 'Taux de rebond',
                'avg_time_on_site' => 'Durée moy.',
            ],
        ],

        'referrers' => [
            'label' => 'Sources de trafic',
            'title_default' => 'Sources de trafic',
            'period' => 'Période',
            'period_desc' => 'Sélectionnez la période de reporting pour les sources de trafic.',
            'period_options' => [
                'day' => 'Jour',
                'week' => 'Semaine',
                'month' => 'Mois',
                'year' => 'Année',
            ],
            'date' => 'Date',
            'date_desc' => 'Sélectionnez la plage de dates pour les sources de trafic.',
            'date_options' => [
                'today' => 'Aujourd\'hui',
                'yesterday' => 'Hier',
                'last7' => '7 derniers jours',
                'last30' => '30 derniers jours',
                'last90' => '90 derniers jours',
            ],
            'selected_period' => 'Période sélectionnée',
            'selected_date' => 'Date sélectionnée',
            'total_visits' => 'Total des visites',
            'no_data' => 'Aucune donnée de source de trafic disponible pour cette période.',
            'types' => [
                'direct' => 'Accès direct',
                'search' => 'Moteurs de recherche',
                'social' => 'Réseaux sociaux',
                'website' => 'Sites web',
                'campaign' => 'Campagnes',
                'ai' => 'Plateformes IA',
                'other' => 'Autres sources',
                'unknown' => 'Source inconnue',
            ],
        ],

        'devices_detection' => [
            'label' => 'Appareils & Navigateurs',
            'title_default' => 'Appareils & Navigateurs',
            'period' => 'Période',
            'period_desc' => 'Sélectionnez la période de reporting pour les appareils et navigateurs.',
            'period_options' => [
                'day' => 'Jour',
                'week' => 'Semaine',
                'month' => 'Mois',
                'year' => 'Année',
            ],
            'date' => 'Date',
            'date_desc' => 'Sélectionnez la plage de dates pour les appareils et navigateurs.',
            'date_options' => [
                'today' => 'Aujourd\'hui',
                'yesterday' => 'Hier',
                'last7' => '7 derniers jours',
                'last30' => '30 derniers jours',
                'last90' => '90 derniers jours',
            ],
            'selected_period' => 'Période sélectionnée',
            'selected_date' => 'Date sélectionnée',
            'device_types_title' => 'Types d\'appareils',
            'browsers_title' => 'Navigateurs',
            'no_data' => 'Aucune donnée d\'appareil ou de navigateur disponible pour cette période.',
            'types' => [
                'desktop' => 'Ordinateur',
                'mobile' => 'Mobile',
                'tablet' => 'Tablette',
                'phablet' => 'Phablette',
                'tv' => 'TV',
                'console' => 'Console',
                'media' => 'Lecteur multimédia',
                'car' => 'Navigateur voiture',
                'camera' => 'Caméra',
                'other' => 'Autres appareils',
                'unknown' => 'Appareil inconnu',
            ],
            'browsers' => [
                'chrome' => 'Chrome',
                'firefox' => 'Firefox',
                'safari' => 'Safari',
                'microsoft_edge' => 'Microsoft Edge',
                'edge' => 'Edge',
                'opera' => 'Opera',
                'samsung_browser' => 'Samsung Browser',
                'samsung' => 'Samsung Browser',
                'brave' => 'Brave',
                'other' => 'Autres navigateurs',
                'unknown' => 'Navigateur inconnu',
            ],
        ],

        'user_country' => [
            'label' => 'Pays',
            'title_default' => 'Top Pays',
            'period' => 'Période',
            'period_desc' => 'Sélectionnez la période de reporting pour les pays.',
            'period_options' => [
                'day' => 'Jour',
                'week' => 'Semaine',
                'month' => 'Mois',
                'year' => 'Année',
            ],
            'date' => 'Date',
            'date_desc' => 'Sélectionnez la plage de dates pour les pays.',
            'date_options' => [
                'today' => 'Aujourd\'hui',
                'yesterday' => 'Hier',
                'last7' => '7 derniers jours',
                'last30' => '30 derniers jours',
                'last90' => '90 derniers jours',
            ],
            'limit' => 'Limite',
            'limit_desc' => 'Nombre maximal de pays à afficher.',
            'limit_options' => [
                5 => '5 pays',
                10 => '10 pays',
                20 => '20 pays',
            ],
            'selected_period' => 'Période sélectionnée',
            'selected_date' => 'Date sélectionnée',
            'selected_limit' => 'Limite',
            'no_data' => 'Aucune donnée de pays disponible pour cette période.',
            'unknown_country' => 'Pays inconnu',
            'unknown_flag' => '🌍',
            'columns' => [
                'country' => 'Pays',
                'nb_visits' => 'Visites',
            ],
        ],

        'embedded_widget' => [
            'label'        => 'Rapport Analytics',
            'report'       => 'Rapport',
            'report_desc'  => 'Rapport analytique à afficher',
            'displayAs'    => 'Afficher comme',
            'displayAs_desc' => 'Comment afficher les données demandées',
            'displayAs_options' => [
                'default'          => 'Défaut',
                'table'            => 'Tableau',
                'tableAllColumns'  => 'Tableau — Toutes les colonnes',
                'tableGoals'       => 'Tableau — Objectifs',
                'cloud'            => 'Nuage de mots',
                'graphPie'         => 'Camembert',
                'graphVerticalBar' => 'Histogramme vertical',
                'graphEvolution'   => 'Évolution',
            ],
            'rows'      => 'Lignes',
            'rows_desc' => 'Nombre d\'éléments à afficher simultanément.',
            'reports' => [
                "widgetReferrersgetReferrerType" => [
                    "title" => "Acquisition - Tous les canaux - Types de canaux",
                    "description" => "Ce tableau présente la répartition des types de canaux. Accès direct : le visiteur a saisi l'URL dans son navigateur. Moteurs de recherche : le visiteur a été redirigé depuis un moteur de recherche. Sites web : le visiteur a suivi un lien depuis un autre site. Campagnes : les visiteurs provenant d'une campagne.",
                ],
                "widgetReferrersgetAll" => [
                    "title" => "Acquisition - Tous les canaux - Sources",
                    "description" => "Ce rapport regroupe toutes vos sources dans un rapport unifié : sites web, mots-clés de recherche et campagnes.",
                ],
                "widgetReferrersgetCampaignUrlBuilder" => [
                    "title" => "Acquisition - Générateur d'URL de campagne",
                    "description" => "",
                ],
                "widgetReferrersgetCampaigns" => [
                    "title" => "Acquisition - Campagnes",
                    "description" => "Ce rapport montre quelles campagnes ont amené des visiteurs sur votre site.",
                ],
                "widgetReferrersgetSparklinesforceView1viewDataTablesparklines" => [
                    "title" => "Acquisition - Vue d'ensemble - Type de canal",
                    "description" => "La vue d'ensemble de l'acquisition montre le pourcentage de trafic par source sur une période sélectionnée.",
                ],
                "widgetReferrersgetEvolutionGraphforceView1viewDataTablegraphEvolutioncolumnsArray" => [
                    "title" => "Acquisition - Vue d'ensemble - Évolution sur la période",
                    "description" => "La vue d'ensemble de l'acquisition montre l'évolution du trafic par canal sur la période sélectionnée.",
                ],
                "widgetReferrersgetKeywords" => [
                    "title" => "Acquisition - Moteurs de recherche & mots-clés - Mots-clés",
                    "description" => "Ce rapport montre les mots-clés recherchés avant d'arriver sur votre site.",
                ],
                "widgetReferrersgetSearchEngines" => [
                    "title" => "Acquisition - Moteurs de recherche & mots-clés - Moteurs de recherche",
                    "description" => "Ce rapport montre quels moteurs de recherche ont référencé des utilisateurs vers votre site.",
                ],
                "widgetReferrersgetSocials" => [
                    "title" => "Acquisition - Réseaux sociaux",
                    "description" => "Ce tableau montre quels réseaux sociaux ont redirigé des visiteurs vers votre site.",
                ],
                "widgetReferrersgetWebsites" => [
                    "title" => "Acquisition - Sites web référents",
                    "description" => "Ce tableau montre quels sites web ont redirigé des visiteurs vers votre site.",
                ],
                "widgetContents" => [
                    "title" => "Comportement - Contenus",
                    "description" => "Le suivi de contenu vous aide à déterminer la popularité de certains éléments de vos pages.",
                ],
                "widgetContentsgetContentNames" => [
                    "title" => "Comportement - Contenus - Nom du contenu",
                    "description" => "Ce rapport montre les noms des contenus vus et avec lesquels les visiteurs ont interagi.",
                ],
                "widgetContentsgetContentPieces" => [
                    "title" => "Comportement - Contenus - Élément de contenu",
                    "description" => "Ce rapport montre les éléments de contenu vus et avec lesquels les visiteurs ont interagi.",
                ],
                "widgetActionsgetDownloads" => [
                    "title" => "Comportement - Téléchargements",
                    "description" => "Ce rapport montre les fichiers téléchargés par vos visiteurs.",
                ],
                "widgetVisitFrequencygetforceView1viewDataTablesparklines" => [
                    "title" => "Comportement - Engagement - Vue d'ensemble de la fréquence",
                    "description" => "Ce rapport compare les métriques des visiteurs réguliers et des nouveaux visiteurs.",
                ],
                "widgetVisitFrequencygetEvolutionGraphforceView1viewDataTablegraphEvolution" => [
                    "title" => "Comportement - Engagement - Visites récurrentes dans le temps",
                    "description" => "Ce rapport montre l'évolution des visites récurrentes dans le temps.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsByDaysSinceLast" => [
                    "title" => "Comportement - Engagement - Visites par jours depuis la dernière visite",
                    "description" => "Ce rapport montre le nombre de visites en fonction du nombre de jours depuis la dernière visite.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsByVisitCount" => [
                    "title" => "Comportement - Engagement - Visites par numéro de visite",
                    "description" => "Ce rapport montre le nombre de visites selon le rang de la visite pour chaque visiteur.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsPerPage" => [
                    "title" => "Comportement - Engagement - Visites par nombre de pages",
                    "description" => "Ce rapport montre combien de visites ont impliqué un certain nombre de pages vues.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsPerVisitDuration" => [
                    "title" => "Comportement - Engagement - Visites par durée",
                    "description" => "Ce rapport montre combien de visites ont eu une certaine durée totale.",
                ],
                "widgetActionsgetEntryPageTitles" => [
                    "title" => "Comportement - Pages d'entrée - Titres",
                    "description" => "Ce rapport contient des informations sur les titres des pages d'entrée.",
                ],
                "widgetActionsgetEntryPageUrls" => [
                    "title" => "Comportement - Pages d'entrée - URLs",
                    "description" => "Ce rapport contient des informations sur les pages d'entrée. Une page d'entrée est la première page vue lors d'une visite.",
                ],
                "widgetEvents" => [
                    "title" => "Comportement - Événements",
                    "description" => "La section Événements propose des rapports sur les événements personnalisés associés à votre site.",
                ],
                "widgetEventsgetActionsecondaryDimensioneventName" => [
                    "title" => "Comportement - Événements - Actions",
                    "description" => "Ce rapport montre le nombre de fois que chaque action d'événement s'est produite.",
                ],
                "widgetEventsgetCategorysecondaryDimensioneventAction" => [
                    "title" => "Comportement - Événements - Catégories",
                    "description" => "Ce rapport montre les catégories de chaque événement suivi et le nombre d'occurrences.",
                ],
                "widgetEventsgetNamesecondaryDimensioneventAction" => [
                    "title" => "Comportement - Événements - Noms",
                    "description" => "Ce rapport montre les noms associés à chaque événement suivi.",
                ],
                "widgetActionsgetExitPageTitles" => [
                    "title" => "Comportement - Pages de sortie - Titres",
                    "description" => "Ce rapport contient des informations sur les titres des pages de sortie.",
                ],
                "widgetActionsgetExitPageUrls" => [
                    "title" => "Comportement - Pages de sortie - URLs",
                    "description" => "Ce rapport contient des informations sur les pages de sortie. Une page de sortie est la dernière page vue lors d'une visite.",
                ],
                "widgetActionsgetOutlinks" => [
                    "title" => "Comportement - Liens sortants",
                    "description" => "Ce rapport affiche la liste hiérarchique des URLs de liens sortants cliqués par vos visiteurs.",
                ],
                "widgetActionsgetPageTitles" => [
                    "title" => "Comportement - Titres de pages",
                    "description" => "Ce rapport contient des informations sur les titres des pages visitées.",
                ],
                "widgetActionsgetPageUrls" => [
                    "title" => "Comportement - Pages - URLs",
                    "description" => "Ce rapport contient des informations sur les URLs visitées, organisées en arborescence.",
                ],
                "widgetPagePerformancegetforceView1viewDataTablesparklines" => [
                    "title" => "Comportement - Performance",
                    "description" => "Ce rapport donne un aperçu de la vitesse à laquelle vos pages deviennent visibles pour vos visiteurs.",
                ],
                "widgetPagePerformancegetEvolutionGraphforceView1viewDataTablegraphStackedBarEvolution" => [
                    "title" => "Comportement - Performance - Évolution des métriques de performance",
                    "description" => "Ce rapport montre l'évolution des métriques de performance de vos pages dans le temps.",
                ],
                "widgetActionsgetPageTitlesforceView1viewDataTabletablePerformanceColumnsperformance1" => [
                    "title" => "Comportement - Performance - Titres de pages",
                    "description" => "Ce rapport contient des informations sur les performances par titre de page.",
                ],
                "widgetActionsgetPageUrlsforceView1viewDataTabletablePerformanceColumnsperformance1" => [
                    "title" => "Comportement - Performance - URLs des pages",
                    "description" => "Ce rapport contient des informations sur les performances par URL de page.",
                ],
                "widgetActionsgetPageTitlesFollowingSiteSearch" => [
                    "title" => "Comportement - Recherche interne - Titres après recherche",
                    "description" => "Ce rapport liste les pages les plus consultées après une recherche interne.",
                ],
                "widgetActionsgetPageUrlsFollowingSiteSearch" => [
                    "title" => "Comportement - Recherche interne - Pages après recherche",
                    "description" => "Ce rapport liste les pages les plus consultées après une recherche interne.",
                ],
                "widgetActionsgetSiteSearchCategories" => [
                    "title" => "Comportement - Recherche interne - Catégories de recherche",
                    "description" => "Ce rapport liste les catégories sélectionnées lors des recherches internes.",
                ],
                "widgetActionsgetSiteSearchNoResultKeywords" => [
                    "title" => "Comportement - Recherche interne - Mots-clés sans résultat",
                    "description" => "Ce rapport liste les mots-clés de recherche n'ayant retourné aucun résultat.",
                ],
                "widgetActionsgetSiteSearchKeywords" => [
                    "title" => "Comportement - Recherche interne - Mots-clés",
                    "description" => "Ce rapport liste les mots-clés recherchés dans le moteur de recherche interne.",
                ],
                "widgetTransitionsgetTransitions" => [
                    "title" => "Comportement - Transitions",
                    "description" => "Le rapport Transitions montre ce que les visiteurs ont fait juste avant et après avoir consulté une page.",
                ],
                "widgetGoalsaddNewGoalidGoal" => [
                    "title" => "Objectifs - Ajouter un nouvel objectif",
                    "description" => "",
                ],
                "widgetGoals" => [
                    "title" => "Objectifs - Vue d'ensemble - Conversions par type de visite",
                    "description" => "La vue d'ensemble des objectifs présente les performances des objectifs définis pour votre site.",
                ],
                "widgetGoalsOverview" => [
                    "title" => "Objectifs - Vue d'ensemble",
                    "description" => "La vue d'ensemble des objectifs présente les performances des objectifs définis pour votre site.",
                ],
                "widgetInsightsgetInsightsOverview" => [
                    "title" => "Insights - Vue d'ensemble",
                    "description" => "",
                ],
                "widgetInsightsgetOverallMoversAndShakers" => [
                    "title" => "Insights - Évolutions notables",
                    "description" => "",
                ],
                "widgetCoreVisualizationssingleMetricViewcolumn" => [
                    "title" => "KPI - Métrique KPI",
                    "description" => "",
                ],
                "widgetSEOgetRank" => [
                    "title" => "SEO - Classements SEO",
                    "description" => "",
                ],
                "widgetBotTrackergetBotTracker" => [
                    "title" => "Visiteurs - BotTracker",
                    "description" => "",
                ],
                "widgetBotTrackergetTop10" => [
                    "title" => "Visiteurs - BotTracker - Top 10 des bots",
                    "description" => "",
                ],
                "widgetBotTrackergetBotTrackerAnzeige" => [
                    "title" => "Visiteurs - Affichage BotTracker",
                    "description" => "",
                ],
                "widgetIPtoCompanygetCompanies" => [
                    "title" => "Visiteurs - Entreprises",
                    "description" => "",
                ],
                "widgetDevicesDetectiongetBrand" => [
                    "title" => "Visiteurs - Appareils - Marque",
                    "description" => "Ce rapport montre les marques / fabricants des appareils utilisés par vos visiteurs.",
                ],
                "widgetDevicesDetectiongetModel" => [
                    "title" => "Visiteurs - Appareils - Modèle",
                    "description" => "Ce rapport montre les modèles d'appareils utilisés par vos visiteurs.",
                ],
                "widgetDevicesDetectiongetType" => [
                    "title" => "Visiteurs - Appareils - Type d'appareil",
                    "description" => "Ce rapport montre les types d'appareils utilisés par vos visiteurs.",
                ],
                "widgetResolutiongetResolution" => [
                    "title" => "Visiteurs - Appareils - Résolution d'écran",
                    "description" => "Ce rapport montre les résolutions d'écran utilisées par vos visiteurs.",
                ],
                "widgetContinent" => [
                    "title" => "Visiteurs - Localisation",
                    "description" => "La section Localisation vous permet de savoir d'où viennent vos visiteurs.",
                ],
                "widgetUserLanguagegetLanguage" => [
                    "title" => "Visiteurs - Localisation - Langue du navigateur",
                    "description" => "Ce rapport montre la langue utilisée par le navigateur des visiteurs.",
                ],
                "widgetUserCountrygetCity" => [
                    "title" => "Visiteurs - Localisation - Ville",
                    "description" => "Ce rapport montre les villes d'où proviennent vos visiteurs.",
                ],
                "widgetUserCountrygetContinent" => [
                    "title" => "Visiteurs - Localisation - Continent",
                    "description" => "Ce rapport montre les continents d'où proviennent vos visiteurs.",
                ],
                "widgetUserCountrygetCountry" => [
                    "title" => "Visiteurs - Localisation - Pays",
                    "description" => "Ce rapport montre les pays d'où proviennent vos visiteurs.",
                ],
                "widgetUserLanguagegetLanguageCode" => [
                    "title" => "Visiteurs - Localisation - Code de langue",
                    "description" => "Ce rapport montre le code de langue exact du navigateur des visiteurs.",
                ],
                "widgetUserCountrygetRegion" => [
                    "title" => "Visiteurs - Localisation - Région",
                    "description" => "Ce rapport montre les régions d'où proviennent vos visiteurs.",
                ],
                "widgetUserCountryMapvisitorMap" => [
                    "title" => "Visiteurs - Localisation - Carte des visiteurs",
                    "description" => "Carte géographique des pays d'où proviennent vos visiteurs.",
                ],
                "widgetVisitsSummarygetEvolutionGraphforceView1viewDataTablegraphEvolution" => [
                    "title" => "Visiteurs - Vue d'ensemble - Visites dans le temps",
                    "description" => "Ce rapport montre l'évolution du nombre de visites sur la période sélectionnée.",
                ],
                "widgetVisitsSummarygetforceView1viewDataTablesparklines" => [
                    "title" => "Visiteurs - Vue d'ensemble - Aperçu des visites",
                    "description" => "Ce rapport fournit un aperçu général du comportement de vos visiteurs.",
                ],
                "widgetLivegetSimpleLastVisitCount" => [
                    "title" => "Visiteurs - Nombre de visiteurs en temps réel",
                    "description" => "",
                ],
                "widgetLivewidget" => [
                    "title" => "Visiteurs - Temps réel - Visites en temps réel",
                    "description" => "Ce rapport montre le flux en temps réel des visites sur votre site.",
                ],
                "widgetUserCountryMaprealtimeMap" => [
                    "title" => "Visiteurs - Carte en temps réel",
                    "description" => "La carte en temps réel montre la localisation des visiteurs sur votre site au cours des 30 dernières minutes.",
                ],
                "widgetDevicesDetectiongetBrowserEngines" => [
                    "title" => "Visiteurs - Logiciels - Moteurs de rendu",
                    "description" => "Ce rapport montre les moteurs de rendu utilisés par vos visiteurs.",
                ],
                "widgetDevicePluginsgetPlugin" => [
                    "title" => "Visiteurs - Logiciels - Plugins navigateur",
                    "description" => "Ce rapport montre les plugins activés dans les navigateurs de vos visiteurs.",
                ],
                "widgetDevicesDetectiongetBrowserVersions" => [
                    "title" => "Visiteurs - Logiciels - Version du navigateur",
                    "description" => "Ce rapport indique quelle version du navigateur utilisaient vos visiteurs.",
                ],
                "widgetDevicesDetectiongetBrowsers" => [
                    "title" => "Visiteurs - Logiciels - Navigateurs",
                    "description" => "Ce rapport indique quel navigateur utilisaient vos visiteurs.",
                ],
                "widgetResolutiongetConfiguration" => [
                    "title" => "Visiteurs - Logiciels - Configurations",
                    "description" => "Ce rapport montre les configurations les plus courantes (OS + navigateur + résolution).",
                ],
                "widgetDevicesDetectiongetOsFamilies" => [
                    "title" => "Visiteurs - Logiciels - Familles de systèmes d'exploitation",
                    "description" => "Ce rapport montre les systèmes d'exploitation utilisés par vos visiteurs, regroupés par famille.",
                ],
                "widgetDevicesDetectiongetOsVersions" => [
                    "title" => "Visiteurs - Logiciels - Versions de systèmes d'exploitation",
                    "description" => "Ce rapport montre les systèmes d'exploitation utilisés par vos visiteurs, version par version.",
                ],
                "widgetVisitTimegetByDayOfWeek" => [
                    "title" => "Visiteurs - Horaires - Visites par jour de la semaine",
                    "description" => "Ce graphique montre le nombre de visites par jour de la semaine.",
                ],
                "widgetVisitTimegetVisitInformationPerLocalTime" => [
                    "title" => "Visiteurs - Horaires - Visites par heure locale",
                    "description" => "Ce graphique montre l'heure locale des visiteurs lors de leurs visites.",
                ],
                "widgetVisitTimegetVisitInformationPerServerTime" => [
                    "title" => "Visiteurs - Horaires - Visites par heure serveur",
                    "description" => "Ce graphique montre l'heure du serveur lors des visites.",
                ],
                "widgetUserIdgetUsers" => [
                    "title" => "Visiteurs - User IDs",
                    "description" => "Ce rapport affiche les visites et les métriques générales pour chaque User ID individuel.",
                ],
                "widgetLivegetVisitorProfilePopup" => [
                    "title" => "Visiteurs - Profil visiteur",
                    "description" => "",
                ],
                "widgetLivegetLastVisitsDetailsforceView1viewDataTableVisitorLogsmall1" => [
                    "title" => "Visiteurs - Journal des visites",
                    "description" => "Le journal des visites vous montre chaque visite de votre site en détail.",
                ],
                "widgetVisitOverviewWithGraph" => [
                    "title" => "Visiteurs - Vue d'ensemble des visites (avec graphique)",
                    "description" => "",
                ],
            ],
        ],
    ],
];
