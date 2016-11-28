#configs

###Directory Description
This directory contains basic configurables of the app. You should be good to go by fine-tuning the settings in network.php and server.php only.App configurables (namely; app.development.php and app.production.php) stand for advanced users.

###Directory Contents
* **server.php** This is perhaps the first and foremost file you need to change. Unless you run the app on vagrant, you change the settings here to match your own Amazon AWS account.
* **network.php** Network related settings such as how many posts to show in the newsfeed or the type of relationships (friendship or follow) you'd like your users to form. Variables self-explanatory.
* **app.development.php** (advanced users only!) php settings. active on the development server.
* **app.production.php** (advanced users only!) php settings. active on the production server.