##Overview

A reference implementation of Google Cloud Messaging(aka. gcm) 
- 3rd party application server in php
- Android client in java

##3rd party application server in php

###Installation

It is a almost zero configuration reference implementation of gcm 3rd party application server written in php. If you already have a running php engine and mysql database on a server, it's never easier than any. 

- Get your api key at [Google API Console](https://code.google.com/apis/console)
- Get you database ready by importing `server/sql/gcm-example_yyyy-mm-dd.sql`.
- Open `server/config.php` and fill out your value. 
- **important** the `$google_api_key` at server and `SENDER_ID` at client must match.
- Upload the code to your test or production server.

###Landing page

For quick peek, visit this address. [http://promote.airplug.com/gcm-example](http://promote.airplug.com/gcm-example)

- Open *your_host/path_to_server_code/* at any Internet browser. Boom! That's it.
- The landing page, which I call 'api test tool' shows you some html form that enables you to send a gcm message to a selected group of clients. **You cannot send a message untill there is at least a record. With bundled client code, you can register or unregister a `registration_id`**
- Ther server's api document is also available.

###Server api endpoint

- From the api test tool's perspective, don't need to know but, it is *your_host/path_to_server_code/api/*

## Android client in java

###Installation

It is a out of box reference code. You can just copy code and paste them into your main/target Android app's desired position.

- Get your sender id from [Google API Console](https://code.google.com/apis/console) that corresponds to your server's api key.
- Open `client/.../src/MainActivity.java` and put the `SENDER_ID` and `urls` value. `urls` is the your *your_host/path_to_server_code/api/*.

##Working example

For quick testing or to get the idea hwo it works,

- Download [gcm_example_client binary](http://promote.airplug.com/gcm-example/res/bin/gcm_example_client.apk) and install it on your Android device. The easist way of installing is to open this page at an Android device and click the link. If it is not applicable, you can email the client binary as attachment and open it at an Android device. Or if you have Android SDK on your machine and an Androdid device connected with a usb cable, issue a command `your_sdk_path/platform-tools/adb install gcm_example_client.apk`. **Since it is not a package from play store, you must set 'Unknown Sources' to ON at the Security section of the System preference.
- Once you are successfully launched the client, press 'register' button to register your `registration_id` to the server.
- And then press 'send' button to populate the server's landing page. There, you can send a gcm message to yourself.

[![gcm-example_api_test_tool.png](http://promote.airplug.com/gcm-example/res/gcm-example_api_test_tool.png)]

###License

MIT

