##Overview

A reference implementation of Google Cloud Messaging(aka. gcm) 
- 3rd party application server in php
- Android client in java

##3rd party application server in php

###Installation

It is a almost zero configuration reference implementation of gcm 3rd party application server written in php. If you already have a running php engine and mysql database on a server, it's never easier than any. 

- Get your api key at [Google API Console](https://code.google.com/apis/console)
- Get your database ready, by importing `server/sql/gcm-example_yyyy-mm-dd.sql`.
- Open `server/config.php` and fill out your value. 
- **important** the `$google_api_key` at server and `SENDER_ID` at client must match.
- Upload the code to your test or production server.

###Landing page

For a quick peek, visit this address. [http://promote.airplug.com/gcm-example](http://promote.airplug.com/gcm-example)

- Open *your_host/path_to_server_code/* at any Internet browser. Boom! That's it.
- The landing page, which I call 'api test tool' shows you some html form that enables you to send a gcm message to a selected group of clients. **You cannot send a message untill there is at least a user in the server. With the bundled client code, you can register or unregister a user, user means `registration_id`**
- At this page ther server's api document is also available.

###Server api endpoint

- It is *your_host/path_to_server_code/api/*

## Android client in java

###Installation

It is a out-of-the-box reference code. What is means is that you just copy code and paste them into your main/target Android app's desired position.

- Get your sender id from [Google API Console](https://code.google.com/apis/console) that corresponds to your server's api key. **sender id is the google api project number**
- Open `client/.../src/MainActivity.java` and put the `SENDER_ID` and `urls` value. `urls` is the api endpoint *your_host/path_to_server_code/api/*.

##Working example

For quick testing or to get the idea hwo it works,

- Download [gcm_example_client binary](http://promote.airplug.com/gcm-example/res/bin/gcm_example_client.apk) and install it on your Android device. The easist way of installing is to open this page at an Android device and click the link. If it is not applicable, you can email the client binary as an attachment and open it at an Android device. Or if you have Android SDK on your machine and an Androdid device connected with a usb cable, issue a command `your_sdk_path/platform-tools/adb install gcm_example_client.apk` at console. **Since the client binary is not a package from play store, you must set 'Unknown Sources' to ON at the Security section of the System preferences.**
- Once you successfully launched the client, press 'Get registration_id' button to get unique id from the Google Connection Server. And then, press `Register` button to save the unique id you've just got. 
- To test it working, press 'Send' button to populate the server's landing page. There, you can send a gcm message to yourself.

![gcm-example_client.png](http://promote.airplug.com/gcm-example/res/img/gcm-example_client.png =220x)

- You can build the provided client code with an arbitrarily-set package name(e.g. com.abc.def, kr.appkr.push...), and test it working against this server, but please do not abuse it.
- Or, you can set a server first and then test it working with the given [gcm_example_client binary](http://promote.airplug.com/gcm-example/res/bin/gcm_example_client.apk). You can manually put YOUR SERVER's api end-point there.

![gcm-example_api_test_tool.png](http://promote.airplug.com/gcm-example/res/img/gcm-example_api_test_tool.png)

###License

MIT

