<?php return [
    'plugin' => [
        'name' => 'Analytics',
        'description' => 'Integrates CA with the Matomo Analytics service',
    ],

    'permissions' => [
        'site' => [
            'view' => 'View analytics data for projects that they have access to',
        ],
    ],

    'components' => [
        'tracker' => [
            'name' => 'Tracker',
            'description' => 'Includes the Matomo tracking code in the theme. Must be placed before the closing </head> tag',
        ],
    ],

    'reportwidgets' => [
        'general' => [
            'period'             => 'Period',
            'period_desc'        => 'The reporting period',
            'period_last_x_days' => 'Last :x days',
        ],

        'embedded_dashboard' => [
            'title_default'      => 'Analytics Dashboard',
        ],

        'embedded_widget' => [
            'label' => 'Analytics Report',
            'report' => 'Report',
            'report_desc' => 'Analytics report to be displayed',
            'displayAs' => 'Display as',
            'displayAs_desc' => 'How to display the requested data',
            'displayAs_options' => [
                'default' => 'default',
                'table' => 'Table',
                'tableAllColumns' => 'Table - All Columns',
                'tableGoals' => 'Table - Goals',
                'cloud' => 'Word Cloud',
                'graphPie' => 'Pie Chart',
                'graphVerticalBar' => 'Vertical Bar Chart',
                'graphEvolution' => 'Evolution',
            ],
            'rows' => 'Rows',
            'rows_desc' => 'Number of individual data elements to display at once.',
            'reports' => [
                "widgetReferrersgetReferrerType" => [
                    "title" => "Acquisition - All Channels - Channel Types",
                    "description" => "This table contains information about the distribution of the channel types.Direct Entry: A visitor has entered the URL in their browser and started browsing on your website - they entered the website directly.Search Engines: A visitor was referred to your website by a search engine.  See the 'Search Engines & Keywords' report for more details.Websites: The visitor followed a link on another website that led to your site.  See the 'Websites' report for more details.Campaigns: Visitors that came to your website as the result of a campaign.  See the 'Campaigns' report for more details.",
                ],
                "widgetReferrersgetAll" => [
                    "title" => "Acquisition - All Channels - Referrers",
                    "description" => "This report shows all your Referrers in one unified report, listing all Websites, Search keywords and Campaigns used by your visitors to find your website.",
                ],
                "widgetReferrersgetCampaignUrlBuilder" => [
                    "title" => "Acquisition - Campaign URL Builder - Campaign URL Builder",
                    "description" => "",
                ],
                "widgetReferrersgetCampaigns" => [
                    "title" => "Acquisition - Campaigns - Campaigns",
                    "description" => "This report shows which campaigns led visitors to your website.",
                ],
                "widgetReferrersgetSparklinesforceView1viewDataTablesparklines" => [
                    "title" => "Acquisition - Overview - Channel Type",
                    "description" => "The Acquisition Overview shows you the percentage of your traffic from all sources over a selected date range.You can also click on a specific channel type to display it within the evolution graph. This can help you discover which channels contribute the most traffic to your site as well as any potential patterns over time. For example, a certain channel may perform better on weekends.",
                ],
                "widgetReferrersgetEvolutionGraphforceView1viewDataTablegraphEvolutioncolumnsArray" => [
                    "title" => "Acquisition - Overview - Evolution Over The Period",
                    "description" => "The Acquisition Overview shows you the percentage of your traffic from all sources over a selected date range.You can also click on a specific channel type to display it within the evolution graph. This can help you discover which channels contribute the most traffic to your site as well as any potential patterns over time. For example, a certain channel may perform better on weekends.",
                ],
                "widgetReferrersgetKeywords" => [
                    "title" => "Acquisition - Search Engines & Keywords - Keywords",
                    "description" => "This report shows which keywords users were searching for before they were referred to your website.  By clicking on a row in the table, you can see the distribution of search engines that were queried for the keyword.Note: This report lists most keywords as not defined, because most search engines do not send the exact keyword used on the search engine.",
                ],
                "widgetReferrersgetSearchEngines" => [
                    "title" => "Acquisition - Search Engines & Keywords - Search Engines",
                    "description" => "This report shows which search engines referred users to your website.  By clicking on a row in the table, you can see what users were searching for using a specific search engine.",
                ],
                "widgetReferrersgetSocials" => [
                    "title" => "Acquisition - Social Networks - Social Networks",
                    "description" => "In this table, you can see which websites referred visitors to your site.  By clicking on a row in the table, you can see which URLs the links to your website were on.",
                ],
                "widgetReferrersgetWebsites" => [
                    "title" => "Acquisition - Websites - Websites",
                    "description" => "In this table, you can see which websites referred visitors to your site.  By clicking on a row in the table, you can see which URLs the links to your website were on.",
                ],
                "widgetContents" => [
                    "title" => "Behaviour - Contents",
                    "description" => "Content tracking helps you determine the popularity of specific pieces of content on any page of your website or app. This section reports the number of impressions and interactions the various pieces of content on your site receive.Learn more in the Content Tracking guide.",
                ],
                "widgetContentsgetContentNames" => [
                    "title" => "Behaviour - Contents - Actions: Content Name",
                    "description" => "This report shows the names of the content your visitors viewed and interacted with.",
                ],
                "widgetContentsgetContentPieces" => [
                    "title" => "Behaviour - Contents - Actions: Content Piece",
                    "description" => "This report shows the pieces of content your visitors viewed and interacted with.",
                ],
                "widgetActionsgetDownloads" => [
                    "title" => "Behaviour - Downloads - Downloads",
                    "description" => "In this report, you can see which files your visitors have downloaded.  What Matomo counts as a download is the click on a download link. Whether the download was completed or not isn't known to Matomo.",
                ],
                "widgetVisitFrequencygetforceView1viewDataTablesparklines" => [
                    "title" => "Behaviour - Engagement - Frequency Overview",
                    "description" => "This report shows general metrics like visits for returning visitors side by side with the same metrics for new visitors. Learn how returning visitors perform overall compared to new visitors.",
                ],
                "widgetVisitFrequencygetEvolutionGraphforceView1viewDataTablegraphEvolution" => [
                    "title" => "Behaviour - Engagement - Returning Visits Over Time",
                    "description" => "The Engagement section provides reports that help to quantify how many new and returning visitors you get. You can also review reports that break down the average time and number of pages per visit, as well as the number of times a visitor has been to your site and the most common number of days between visits.This can help you to optimise for frequency and high-interaction visits in addition to maximising your reach.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsByDaysSinceLast" => [
                    "title" => "Behaviour - Engagement - Visits By Days Since Last Visit",
                    "description" => "In this report, you can see how many visits were from visitors whose last visit was a certain number of days ago.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsByVisitCount" => [
                    "title" => "Behaviour - Engagement - Visits By Visit Number",
                    "description" => "In this report, you can see the number of visits who were the Nth visit, ie. visitors who visited your website at least N times.Please note, that you can view the report in other ways than as a tag cloud. Use the controls at the bottom of the report to do so.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsPerPage" => [
                    "title" => "Behaviour - Engagement - Visits Per Number Of Pages",
                    "description" => "In this report, you can see how many visits involved a certain number of pageviews. Initially, the report is shown as a tag cloud, more common numbers of pages are displayed in a larger font.Please note, that you can view the report in other ways than as a tag cloud. Use the controls at the bottom of the report to do so.",
                ],
                "widgetVisitorInterestgetNumberOfVisitsPerVisitDuration" => [
                    "title" => "Behaviour - Engagement - Visits Per Visit Duration",
                    "description" => "In this report, you can see how many visits had a certain total duration. Initially, the report is shown as a tag cloud, more common durations are displayed in a larger font.Please note, that you can view the report in other ways than as a tag cloud. Use the controls at the bottom of the report to do so.",
                ],
                "widgetActionsgetEntryPageTitles" => [
                    "title" => "Behaviour - Entry Pages - Entry Page Titles",
                    "description" => "This report contains information about the titles of entry pages that were used during the specified period. Use the plus and minus icons on the left to navigate.",
                ],
                "widgetActionsgetEntryPageUrls" => [
                    "title" => "Behaviour - Entry Pages - Entry Pages",
                    "description" => "This report contains information about the entry pages that were used during the specified period. An entry page is the first page that a user views during their visit.  The entry URLs are displayed as a folder structure.Use the plus and minus icons on the left to navigate.",
                ],
                "widgetEvents" => [
                    "title" => "Behaviour - Events",
                    "description" => "The Events section offers reports on the custom events associated with your site. Events typically require custom configuration. Once configured you can review reports broken down by category, action and name.Learn more about event tracking here.",
                ],
                "widgetEventsgetActionsecondaryDimensioneventName" => [
                    "title" => "Behaviour - Events - Actions: Event Actions",
                    "description" => "This report shows you the number of times each event action occurred. You can view the event categories and names that were tracked along with each event action in the row's subtable. You can change which is shown by changing the secondary dimension with the link at the bottom of the report.",
                ],
                "widgetEventsgetCategorysecondaryDimensioneventAction" => [
                    "title" => "Behaviour - Events - Actions: Event Categories",
                    "description" => "This report shows the categories of each tracked event and how many times they occurred. You can view the event actions and names that were tracked along with each event category in each row's subtable. You can change which is shown by changing the secondary dimension with the link at the bottom of the report.",
                ],
                "widgetEventsgetNamesecondaryDimensioneventAction" => [
                    "title" => "Behaviour - Events - Actions: Event Names",
                    "description" => "This report shows you the names associated with each tracked event and how many times they occurred. You can view the event actions and categories that were tracked along with each event name in each row's subtable. You can change which is shown by changing the secondary dimension with the link at the bottom of the report.",
                ],
                "widgetActionsgetExitPageTitles" => [
                    "title" => "Behaviour - Exit Pages - Exit Page Titles",
                    "description" => "This report contains information about the titles of exit pages that occurred during the specified period. Use the plus and minus icons on the left to navigate.",
                ],
                "widgetActionsgetExitPageUrls" => [
                    "title" => "Behaviour - Exit Pages - Exit Pages",
                    "description" => "This report contains information about the exit pages that occurred during the specified period. An exit page is the last page that a user views during their visit.  The exit URLs are displayed as a folder structure.Use the plus and minus icons on the left to navigate.",
                ],
                "widgetActionsgetOutlinks" => [
                    "title" => "Behaviour - Outlinks - Outlinks",
                    "description" => "This report shows a hierarchical list of outlink URLs that were clicked by your visitors. An outlink is a link that leads the visitor away from your website (to another domain).Use the plus and minus icons on the left to navigate.",
                ],
                "widgetActionsgetPageTitles" => [
                    "title" => "Behaviour - Page Titles - Page Titles",
                    "description" => "This report contains information about the titles of the pages that have been visited.  The page title is the HTML  Tag that most browsers show in their window title.",
                ],
                "widgetActionsgetPageUrls" => [
                    "title" => "Behaviour - Pages - Pages",
                    "description" => "This report contains information about the page URLs that have been visited.  The table is organized hierarchically, the URLs are displayed as a folder structure.Use the plus and minus icons on the left to navigate.",
                ],
                "widgetPagePerformancegetforceView1viewDataTablesparklines" => [
                    "title" => "Behaviour - Performance",
                    "description" => "This report provides an overview of how fast your webpages become visible to your visitors. This includes both how long it takes for browsers to download your webpages and how long it takes for browsers to display them.",
                ],
                "widgetPagePerformancegetEvolutionGraphforceView1viewDataTablegraphStackedBarEvolution" => [
                    "title" => "Behaviour - Performance - Evolution Of Page Performance Metrics",
                    "description" => "The Performance section can help you analyse how fast your website or app is performing on the whole and help discover whether you have specific pages that significantly deviate from your averages.You can also find reports showing exactly how long each page of your website takes to load and what is contributing to their loading time.",
                ],
                "widgetActionsgetPageTitlesforceView1viewDataTabletablePerformanceColumnsperformance1" => [
                    "title" => "Behaviour - Performance - Page Titles",
                    "description" => "This report contains information about the titles of the pages that have been visited.  The page title is the HTML  Tag that most browsers show in their window title.",
                ],
                "widgetActionsgetPageUrlsforceView1viewDataTabletablePerformanceColumnsperformance1" => [
                    "title" => "Behaviour - Performance - Page URLs",
                    "description" => "This report contains information about the page URLs that have been visited.  The table is organized hierarchically, the URLs are displayed as a folder structure.Use the plus and minus icons on the left to navigate.",
                ],
                "widgetActionsgetPageTitlesFollowingSiteSearch" => [
                    "title" => "Behaviour - Site Search - Page Titles Following A Site Search",
                    "description" => "When visitors search on your website, they are looking for a particular page, content, product, or service. This report lists the pages that were clicked the most after an internal search. In other words, the list of pages the most searched for by visitors already on your website.Use the plus and minus icons on the left to navigate.",
                ],
                "widgetActionsgetPageUrlsFollowingSiteSearch" => [
                    "title" => "Behaviour - Site Search - Pages Following A Site Search",
                    "description" => "When visitors search on your website, they are looking for a particular page, content, product, or service. This report lists the pages that were clicked the most after an internal search. In other words, the list of pages the most searched for by visitors already on your website.Use the plus and minus icons on the left to navigate.",
                ],
                "widgetActionsgetSiteSearchCategories" => [
                    "title" => "Behaviour - Site Search - Search Categories",
                    "description" => "This report lists the Categories that visitors selected when they made a Search on your website.For example, Ecommerce websites typically have a 'Category' selector so that visitors can restrict their searches to all products in a specific Category.",
                ],
                "widgetActionsgetSiteSearchNoResultKeywords" => [
                    "title" => "Behaviour - Site Search - Search Keywords With No Results",
                    "description" => "Tracking searches that visitors make on your website is a very effective way to learn more about what your audience is looking for, it can help find ideas for new content, new Ecommerce products that potential customers might be searching for, and generally improve the visitors' experience on your website.This report lists the Search Keywords that did not return any Search result: maybe the search engine algorithm can be improved, or maybe your visitors are looking for content that is not (yet) on your website?",
                ],
                "widgetActionsgetSiteSearchKeywords" => [
                    "title" => "Behaviour - Site Search - Site Search Keywords",
                    "description" => "This report lists the Search Keywords that visitors searched for on your internal Search Engine.Tracking searches that visitors make on your website is a very effective way to learn more about what your audience is looking for, it can help find ideas for new content, new Ecommerce products that potential customers might be searching for, and generally improve the visitors' experience on your website.",
                ],
                "widgetTransitionsgetTransitions" => [
                    "title" => "Behaviour - Transitions - Transitions",
                    "description" => "Transitions gives you a report that shows the things your visitors did directly before and after viewing a certain page. This page will explain how to access, understand, and use the powerful Transitions report.More Details",
                ],
                "widgetGoalsaddNewGoalidGoal" => [
                    "title" => "Goals - Add A New Goal - Add A New Goal",
                    "description" => "",
                ],
                "widgetGoals" => [
                    "title" => "Goals - Overview - Conversions Overview By Type Of Visit",
                    "description" => "The Goals Overview reports on the performance of the goals defined for your website. You can access your goal’s conversion percentages, amount of revenue generated and full reports for each.Click on an individual metric within the sparkline chart to focus on it within the full-sized evolution graph.",
                ],
                "widgetGoalsOverview" => [
                    "title" => "Goals - Overview - Overview",
                    "description" => "The Goals Overview reports on the performance of the goals defined for your website. You can access your goal’s conversion percentages, amount of revenue generated and full reports for each.Click on an individual metric within the sparkline chart to focus on it within the full-sized evolution graph.",
                ],
                "widgetInsightsgetInsightsOverview" => [
                    "title" => "Insights - Insights Overview",
                    "description" => "",
                ],
                "widgetInsightsgetOverallMoversAndShakers" => [
                    "title" => "Insights - Movers And Shakers",
                    "description" => "",
                ],
                "widgetCoreVisualizationssingleMetricViewcolumn" => [
                    "title" => "KPI Metric - KPI Metric",
                    "description" => "",
                ],
                "widgetSEOgetRank" => [
                    "title" => "SEO - SEO Rankings",
                    "description" => "",
                ],
                "widgetBotTrackergetBotTracker" => [
                    "title" => "Visitors - BotTracker - BotTracker",
                    "description" => "",
                ],
                "widgetBotTrackergetTop10" => [
                    "title" => "Visitors - BotTracker - Top 10 Bots",
                    "description" => "",
                ],
                "widgetBotTrackergetBotTrackerAnzeige" => [
                    "title" => "Visitors - BotTracker Display",
                    "description" => "The Visitors pages tell you things about who your visitors are. Things like where your visitors came from, what devices and browsers they're using and when they generally visit your website. Understand, in the aggregate, who your audience is, and look for outliers to see how your audience could grow.In addition to general information about your visitors, you can also use the Visits Log to see what occurred in every individual visit.",
                ],
                "widgetIPtoCompanygetCompanies" => [
                    "title" => "Visitors - Companies - Companies",
                    "description" => "",
                ],
                "widgetDevicesDetectiongetBrand" => [
                    "title" => "Visitors - Devices - Device Brand",
                    "description" => "This report shows the brands / manufacturers of the devices your visitors were using. In most cases this information is only available for non-desktop devices.",
                ],
                "widgetDevicesDetectiongetModel" => [
                    "title" => "Visitors - Devices - Device Model",
                    "description" => "This report shows the devices your visitors are using. Each model is displayed combined with the device brand as some model names are used by multiple brands.",
                ],
                "widgetDevicesDetectiongetType" => [
                    "title" => "Visitors - Devices - Device Type",
                    "description" => "This report shows the types of devices your visitors were using. This report will always show all device types Matomo is able to detect, even if there were no visits with a specific type.",
                ],
                "widgetResolutiongetResolution" => [
                    "title" => "Visitors - Devices - Screen Resolution",
                    "description" => "This report shows the screen resolutions your visitors used when viewing your website.",
                ],
                "widgetContinent" => [
                    "title" => "Visitors - Locations",
                    "description" => "The Locations section is the best way to find out where people are when they visit your site. It reveals the countries, continents, regions, cities that you visitors come from, in tables and map form. Additionally, you can see what language their browser is set to, helping identify international visitors in alternative locations.",
                ],
                "widgetUserLanguagegetLanguage" => [
                    "title" => "Visitors - Locations - Browser Language",
                    "description" => "This report shows which language the visitor's browsers are using. (e.g. 'English')",
                ],
                "widgetUserCountrygetCity" => [
                    "title" => "Visitors - Locations - City",
                    "description" => "This report shows the cities your visitors were in when they accessed your website.In order to see data for this report, you must setup GeoIP in the Geolocation admin tab. The commercial Maxmind GeoIP databases are more accurate than the free ones. To see how accurate they are, click here.",
                ],
                "widgetUserCountrygetContinent" => [
                    "title" => "Visitors - Locations - Continent",
                    "description" => "This report shows which continent your visitors were in when they accessed your website.",
                ],
                "widgetUserCountrygetCountry" => [
                    "title" => "Visitors - Locations - Country",
                    "description" => "This report shows which country your visitors were in when they accessed your website.",
                ],
                "widgetUserLanguagegetLanguageCode" => [
                    "title" => "Visitors - Locations - Language Code",
                    "description" => "This report shows which exact language code the visitor's browsers is set to. (e.g. 'German - Austria (de-at)')",
                ],
                "widgetUserCountrygetRegion" => [
                    "title" => "Visitors - Locations - Region",
                    "description" => "This report shows which region your visitors were in when they accessed your website.In order to see data for this report, you must setup GeoIP in the Geolocation admin tab. The commercial Maxmind GeoIP databases are more accurate than the free ones. To see how accurate they are, click here.",
                ],
                "widgetUserCountryMapvisitorMap" => [
                    "title" => "Visitors - Locations - Visitor Map",
                    "description" => "The Locations section is the best way to find out where people are when they visit your site. It reveals the countries, continents, regions, cities that you visitors come from, in tables and map form. Additionally, you can see what language their browser is set to, helping identify international visitors in alternative locations.",
                ],
                "widgetVisitsSummarygetEvolutionGraphforceView1viewDataTablegraphEvolution" => [
                    "title" => "Visitors - Overview - Visits Over Time",
                    "description" => "The Visitors Overview helps you understand the popularity of your site. It does this by providing charts that show how many visits your site is receiving over a selected period and the average level of engagement for key features, such as searches and downloads.",
                ],
                "widgetVisitsSummarygetforceView1viewDataTablesparklines" => [
                    "title" => "Visitors - Overview - Visits Overview",
                    "description" => "This report provides a very general overview of how your visitors behave.",
                ],
                "widgetLivegetSimpleLastVisitCount" => [
                    "title" => "Visitors - Real Time Visitor Count",
                    "description" => "The Visitors pages tell you things about who your visitors are. Things like where your visitors came from, what devices and browsers they're using and when they generally visit your website. Understand, in the aggregate, who your audience is, and look for outliers to see how your audience could grow.In addition to general information about your visitors, you can also use the Visits Log to see what occurred in every individual visit.",
                ],
                "widgetLivewidget" => [
                    "title" => "Visitors - Real-time - Visits In Real-time",
                    "description" => "The Visits in Real-time report shows the real-time flow of visits to your website. It includes a real-time counter of your visits and page views in the last 24 hours and the previous 30 minutes.This report refreshes every 5 seconds and displays new visits (or existing visitors that view a new page) at the top of the list with a fade-in effect.",
                ],
                "widgetUserCountryMaprealtimeMap" => [
                    "title" => "Visitors - Real-time Map - Real-time Map",
                    "description" => "The Real-time Map shows the location of visitors on your site within the last 30 minutes. Large orange bubbles represent more recent visits, while smaller grey bubbles represent older visits. This data refreshes every five seconds, and new visitors appear with a flashing effect.",
                ],
                "widgetDevicesDetectiongetBrowserEngines" => [
                    "title" => "Visitors - Software - Browser Engines",
                    "description" => "This report shows your visitors' browsers broken down into browser engines.  The most important information for web developers is what kind of rendering engine their visitors are using. The labels contain the names of the engines followed by the most common browser using that engine in brackets.",
                ],
                "widgetDevicePluginsgetPlugin" => [
                    "title" => "Visitors - Software - Browser Plugins",
                    "description" => "This report shows which browser plugins your visitors had enabled. This information might be important for choosing the right way to deliver your content.",
                ],
                "widgetDevicesDetectiongetBrowserVersions" => [
                    "title" => "Visitors - Software - Browser Version",
                    "description" => "This report contains information about what kind of browser your visitors were using. Each browser version is listed separately.",
                ],
                "widgetDevicesDetectiongetBrowsers" => [
                    "title" => "Visitors - Software - Browsers",
                    "description" => "This report contains information about what kind of browser your visitors were using.",
                ],
                "widgetResolutiongetConfiguration" => [
                    "title" => "Visitors - Software - Configurations",
                    "description" => "This report shows the most common overall configurations that your visitors had. A configuration is the combination of an operating system, a browser type and a screen resolution.",
                ],
                "widgetDevicesDetectiongetOsFamilies" => [
                    "title" => "Visitors - Software - Operating System Families",
                    "description" => "This report shows you the operating systems your visitors are using grouped by operating system family. An operating system family consists of different versions or distributions.",
                ],
                "widgetDevicesDetectiongetOsVersions" => [
                    "title" => "Visitors - Software - Operating System Versions",
                    "description" => "This report shows you the operating systems your visitors are using. Each version and distribution is shown separately.",
                ],
                "widgetVisitTimegetByDayOfWeek" => [
                    "title" => "Visitors - Times - Visits By Day Of Week",
                    "description" => "This graph shows the number of visits your website received on each day of the week.",
                ],
                "widgetVisitTimegetVisitInformationPerLocalTime" => [
                    "title" => "Visitors - Times - Visits Per Local Time",
                    "description" => "This graph shows what time it was in the  visitors' time zones  during their visits.",
                ],
                "widgetVisitTimegetVisitInformationPerServerTime" => [
                    "title" => "Visitors - Times - Visits Per Server Time",
                    "description" => "This graph shows what time it was in the  server's time zone  during the visits.",
                ],
                "widgetUserIdgetUsers" => [
                    "title" => "Visitors - User IDs - User IDs",
                    "description" => "This report shows visits and other general metrics for every individual User ID.",
                ],
                "widgetLivegetVisitorProfilePopup" => [
                    "title" => "Visitors - Visitor Profile",
                    "description" => "The Visitors pages tell you things about who your visitors are. Things like where your visitors came from, what devices and browsers they're using and when they generally visit your website. Understand, in the aggregate, who your audience is, and look for outliers to see how your audience could grow.In addition to general information about your visitors, you can also use the Visits Log to see what occurred in every individual visit.",
                ],
                "widgetLivegetLastVisitsDetailsforceView1viewDataTableVisitorLogsmall1" => [
                    "title" => "Visitors - Visits Log - Visits Log",
                    "description" => "The Visits Log shows you every visit your website receives in detail. You can see what actions each visitor has taken, how they got to your site, a bit about who they are, and more (while still complying with your local privacy regulations).While other reports in Matomo show how your visitors behave at an aggregate level, the Visits Log provides granular detail. You can also use segments to narrow it down to specific types of visits to understand your visitors better.Learn more in the Visits Log guide.",
                ],
                "widgetVisitOverviewWithGraph" => [
                    "title" => "Visitors - Visits Overview (with Graph)",
                    "description" => "The Visitors pages tell you things about who your visitors are. Things like where your visitors came from, what devices and browsers they're using and when they generally visit your website. Understand, in the aggregate, who your audience is, and look for outliers to see how your audience could grow.In addition to general information about your visitors, you can also use the Visits Log to see what occurred in every individual visit.",
                ],
            ],
        ],
    ],
];