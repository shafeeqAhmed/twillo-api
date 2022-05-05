## step #1

clone the project

## step #2

```bash
$ composer install

```

## step #3

open your env file and update followin parameters

-3.1 set app url

# APP_URL=https://colony.rocks

-3.2 set front-end app url

# FRONT_END_APP=colony.rocks

-3.3 set database credential

# DB_DATABASE=

# DB_USERNAME=

# DB_PASSWORD=

-3.4 set pusher information

# PUSHER_APP_ID=1234567

# PUSHER_APP_KEY=1234594556a4568cae7f

# PUSHER_APP_SECRET=cf8d08ec47f973212345

# PUSHER_APP_CLUSTER=mt1

-3.5 set twilio account credentails

# TWILIO_SID=

# TWILIO_TOKEN=

-3.6 set web hook for twilio

# WEB_HOOK="${APP_URL}/twillo-api/api/twilio_webhook"
