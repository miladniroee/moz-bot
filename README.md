# Moz Bot 🍌


In those strange days, when the internet had turned Iran into a lonely island
<br>
and the news of war was pouring in from every side...
<br>
I built a bot...
<br>
Not for anything great. Not to save the world.

Its only job was this:
<br>
Whenever someone said the word "موز" 🍌 in a group chat or private message,
it would send a random sentence about bananas.
<br>
That's it.
<br>
A wonderfully useless little bot.

The reason was simple:
<br>
I wanted a corner of my mind
<br>
that was cool, silly, and disconnected from reality.
<br>
A place where bananas could talk,
<br>
and people could laugh for no reason 😊
<br>


Later, for my closest friends,
<br>
I made a secret version:
<br>
Instead of bananas, it would send them strawberries 🍓, mangoes 🥭, and watermelons 🍉
<br>
For the ones who knew exactly what war meant
<br>
and still chose to laugh.




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