Socializr
=========

Social web app for clubs

Getting dev-box up and running
------------------------------

* Install [Virtualbox 4.2](https://www.virtualbox.org/wiki/Download_Old_Builds_4_2)
* Install [Vagrant 1.3.5](http://downloads.vagrantup.com/tags/v1.3.5)
* Make sure virtualization is enabled in your BIOS configuration
* For the first install the Debian box has to connect to the package repo, this connection needs authorization connection on a different port then :80. Make sure internet connections over other ports are available.

Open a (git) bash terminal and go to the local socializr git folder and run:

```bash
#Start the devbox from repo root
$ vagrant up
#Wait for the image to be downloaded...
```

Output looks like this:
```bash
Bringing machine 'default' up with 'virtualbox' provider...
[default] Importing base box 'socializr'...
[default] Matching MAC address for NAT networking...
[default] Setting the name of the VM...
[default] Clearing any previously set forwarded ports...
[default] Creating shared folders metadata...
[default] Clearing any previously set network interfaces...
[default] Preparing network interfaces based on configuration...
[default] Forwarding ports...
[default] -- 22 => 2222 (adapter 1)
[default] Running 'pre-boot' VM customizations...
[default] Booting VM...
[default] Waiting for machine to boot. This may take a few minutes...
[default] Machine booted and ready!
[default] Configuring and enabling network interfaces...
[default] Mounting shared folders...
[default] -- /vagrant
[default] -- /tmp/vagrant-puppet/manifests
[default] Running provisioner: shell...

 ____        ____  _   _ ____      _      generated using
|  _ \ _   _|  _ \| | | |  _ \ ___| |_   ___ ___  _ __ ___
| |_) | | | | |_) | |_| | |_) / _ \ __| / __/ _ \| '_ ` _ \
|  __/| |_| |  __/|  _  |  __/  __/ |_ | (_| (_) | | | | | |
|_|    \__,_|_|   |_| |_|_|   \___|\__(_)___\___/|_| |_| |_|

Created directory /.puphpet-stuff
Running initial-setup apt-get update#

.........
```

Installing the application libs with composer
---------------------------------------------

```bash
#Log in on the dev-box and run composer
$ vagrant ssh
$ cd /vagrant
$ composer update
```

Application set-up
------------------

* ```./web``` is the app root folder and is exposed to the webserver
* App libraries are installed via composer in ```./vendor```
* Add the following to your hosts file: ```192.168.56.110 socializr.io api.socializr.io mongo.socializr.io webgrind.socializr.io```
* At [mongo.socializr.io](http://mongo.socializr.io) you can manage your mongodb data via an php admin interface
* For better performance,

Features which need to be included
----------------------------------

#### Registration and personal information ####
- [ ] Registration with e-mail verification
- [x] Editing and updating your personal data
- [ ] Account disabling

#### Login ####
- [x] Login to your account
- [ ] Remember login for computer

#### Groups and group management ####
- [ ] Every member can start a group
- [ ] Every group has one or more administrators
- [ ] Admins can send invites for a group
- [ ] Admins kan remove users from a group

#### Group types ####
- There can be 3 types of groups:
    - [ ] Open, accessible to any user
    - [ ] Closed, accessible on invite
    - [ ] Secret, accessible on invite
- [ ] Once a group is create the type can't be changed

#### Personal dashboard ####
- [ ] Groups you are part of
- [ ] News messages
- [ ] Update from posts
- [ ] Seach the network

#### Group pages (public or private acc. to type) ####
- [ ] Image gallery
- [ ] Movie gallery
- [ ] Rich text blocks
- [ ] Wall for posts
- [ ] Admin message board

#### Emails ####
- [ ] Verification mails (registration/password change/email change)
- [ ] Verification (becoming member of a group)
- [ ] Invites for groups
- [ ] Weekly summary of news and updates
- [ ] Settings for update mails can be managed

#### Search ####
- [ ] Searching for members and groups can be accessed by anyone
- [ ] Searching through names and/or descriptions of groups is optional
- [ ] Closed and secret groups can only be found by its members

#### WebServices ####
- [ ] Messages with a twitter hash can be fetched from Twitter
- [x] Facebook/Google+ integration optional

#### Bonus: ####
- [x] Login via OpenID provider (Google/Facebook/Twitter)
- [x] The social network is available via a RESTful API



