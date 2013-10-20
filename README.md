Socializr
=========

Social web app for clubs

Getting dev up and running
--------------------------

* Install [Virtualbox 4.2](https://www.virtualbox.org/wiki/Download_Old_Builds_4_2)
* Install [Vagrant 1.3.5](http://downloads.vagrantup.com/tags/v1.3.5)

```bash
#Start the devbox from repo root
$ vagrant up
```

Features which need to be included
----------------------------------

#### Registration and personal information ####
* Registration with e-mail verification
* Editing your personal data
* Account disabling

#### Login ####
* Login to your account
* Remember login for computer

#### Groups and group management ####
* Every member can start a group
* Every group has one or more administrators
* Admins can send invites for a group
* Admins kan remove users from a group

#### Group types ####
* There can be 3 types of groups:
    * Open, accessible to any user
    * Closed, accessible on invite
    * Secret, accessible on invite
* Once a group is create the type can't be changed

#### Personal dashboard ####
* Groups you are part of
* News messages
* Update from posts
* Seach the network

#### Group pages (public or private acc. to type) ####
* Image gallery
* Movie gallery
* Rich text blocks
* Wall for posts
* Admin message board

#### Emails ####
* Verification mails (registration/password change/email change)
* Verification (becoming member of a group)
* Invites for groups
* Weekly summary of news and updates
* Settings for update mails can be managed

#### Search ####
* Searching for members and groups can be accessed by anyone
* Searching through names and/or descriptions of groups is optional
* Closed and secret groups can only be found by its members

#### WebServices ####
* Messages with a twitter hash can be fetched from Twitter
* Facebook/Google+ integration optional

#### Bonus: ####
* Login via OpenID provider (Google/Facebook/Twitter)
* The social network is available via a RESTful API

