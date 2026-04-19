# Moz Bot 🍌

## usage:

* Create .env file from .env.example
* Create an app key on .env (it could be anything)
* Run sql files in order from `App/Database` directory
* set webhook to `https://example.com/index.php?sec=<APP_KEY>`

> `APP_KEY` should be url encoded when you are setting it on webhook.

### Personalize data
You can create a `PersonalizeText` class on `App/Utils` directory for personalizing random text sending.

Sample class could be found in `App/Utils` Directory.

### Contribution
You can add and edit texts from `App/Texts` Directory