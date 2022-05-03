# Matomo Analytics

![Matomo Plugin](https://user-images.githubusercontent.com/15900351/156275611-363b795f-bea8-47f2-b6dc-d7852ad5efcd.png)

[Matomo](https://matomo.org) is a Google Analytics alternative that lets website owners own their analytical data and protect their customers' privacy. This is a [WinterCMS](https://wintercms.com) plugin that integrates Matomo into WinterCMS providing reporting widgets, client-based tracking component, and (planned) a server-side tracking component.

## Prerequisites

You must have access to Matomo, either through the cloud services provided by [Matomo](https://matomo.org) or a [self hosted Matomo instance](https://matomo.org/matomo-on-premise/). If you are paranoid about privacy, you might want to go for a self-hosted instance.

## Plugin Usage
You must have basic knowledge about Matomo in order to use this plugin. In particular, you must know how to create new tracking instances, users, and security tokens. Don't be worried. This is quite easy to learn by playing around with Matomo.

### Setup site on Matomo

To get Matomo Analytics setup for your Winter CMS instance:

- identify the analytics server you intend to use (e.g., matomo.org).
- in the Matoma backend set your CMS server up for tracking and note down the site ID
- again in the in the Matoma backend generate a security token (an *Auth Token*). Note that the *Auth Token* must be created under user who does **not** have super admin access
- add the Matomo Server URL, the Site ID, and the Auth Token to this plugin's configuration

>**NOTE:** You may want to disable any adblockers that you are currently using.

>**NOTE:** Remember the tracker is disabled when authenticated backend users are detected or the website is in maintenance mode

### Configuration

Configuration for this plugin is handled through a [configuration file](https://wintercms.com/docs/plugin/settings#file-configuration). In order to modify the configuration values and get started you can either add the values to your `.env` environment file or copy the `plugins/winter/matomo/config/config.php` file to `config/winter/matomo/config.php` and make your changes there.

Environment File Supported Values:
- `MATOMO_SERVER="https://example.matomo.cloud/"`
- `MATOMO_SITE_ID=1`
- `MATOMO_TOKEN=""`

### Tracking
To start tracking your visitors simply add the `Tracker` component to the `<head>` section on all of the pages that you want to include the Matomo tracker on.

>**NOTE:** The tracker component must be rendered before the closing `</head>` tag.

With that, Matomo should start tracking and you should see results shortly.

> **NOTE:** Matomo provides near-realtime results with an emphasis on "near", so you might have to wait a few minutes to see the first results.

### Reporting
This plugin provides two main Dashboard ReportWidgets out of the box.

The first widget is the "Analytics Dashboard" widget, which provides the entire Matomo dashboard embedded into the Winter dashboard through the use of an iframe. It is recommended that you set this widget to the maximum width in order to see all of the data present easily. This widget supports changing the period of time that is reported on.

The second widget is the "Analytics Report" widget, which provides the ability to pick from a pre-set list of common reports that can be embedded as individual widgets on the Winter Dashboard. Each of the widgets allows you to configure the period of time that is examined, how the data is displayed, and how many rows to include in the results. It is also possible to export the data of an individual report.

## Credits
This plugin was originally written by Helmut Kaufmann, Küssnacht am Rigi, in Switzerland. Helmut can be reached by mail at <software@mercator.li>.

It has since been modified and re-released under the Winter namespace as a first party plugin for Winter CMS maintained by the Winter CMS team and Helmut Kaufmann.

If you would like to contribute to this plugin's development, please feel free to submit issues or pull requests to the plugin's repository here: https://github.com/wintercms/wn-matomo-plugin

If you would like to support Helmut Kaufmann please visit [PayPal](https://www.paypal.com/donate/?hosted_button_id=MZYBN2NEDEDNC).

If you would like to support Winter CMS, please visit [WinterCMS.com](https://wintercms.com/support)
