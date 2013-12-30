API Urls
========

Login and authorization
-----------------------

```
GET /
```
* Redirect to https://socializr.io


```
GET /login
```
* Return login uri's or redirect to https://socializr.io/#/user/profile


```
GET /loginfailed
```
* Show error with nice message about the login failing
* Gets called when authorization with login provider fails

```
GET /logout?_csrf_token={token}
```
* Log the user out
* A valid csrf token can be retrieved by requesting the current user ```GET /user/current```

```
GET /auth/......
```
* URI's for login service providers [Google, Facebook and Twitter]


User
-----------------------

```
GET /user/?limit={20}&offset={0}
```
* Get all users

```
GET /user/current
```
* Get the user details for the currently logged-in user

```
GET /user/{id}
```
* Get the user details

```
GET /user/current/news
```
* Get the news feed for the current logged-in user

```
GET /user/invite/{hash}
```
* Accept an invitation to a group with given hash
* This request redirects to https://socializr.io/group/{groupId} for the groupId of the invitation


Group
-----------------------

```
GET /group/?limit={20}&offset={0}
```
* Get all groups where the current user is permitted READ-access

```
GET /group/{id}
```
* Get the group details if the current user is permitted READ-access

*Member requests:*

```
GET /group/{groupId}/board?limit={20}&offset={0}
```
* Get the boards for a group if the current user is permitted MEMBER-access

```
POST /group/
```
* Create a new group and give the current user ADMIN-access

```
POST /group/{groupId}/board
```
* Create a new board for the group if the current user is permitted MEMBER-access

*Administrator requests:*

```
GET /group/{groupId}/invite/{userId}
```
* Invite the user with {userId} for membership of this group

```
GET /group/{groupId}/block/{userId}
```
* Block the user with {userId} for this group

```
GET /group/{groupId}/promote/{userId}
```
* Make the user with {userId} admin for this group !IRREVERSIBLE!


Board
---------------

```
GET /board/{id}
```
* Get the board details if the current user has MEMBER-access to the related group

```
GET /board/{boardId}/message
```
* Get the messages for the board if the current user has MEMBER-access to the related group

```
POST /board/{boardId}/message
```
* Create a messages for a board if the current user has MEMBER-access to the related group


Message
---------------

```
GET /message/{id}
```
* Get the message details if the current user has MEMBER-access to the related group


Search
---------------

```
GET /search/{query}?type={group|user}limit={20}&offset={0}
```
* Search user names, group names and group descriptions with the {query}
* The type parameter filters the search on [group|user]
* Groups and users are separated in the search result
* Limit and offset are used separate for groups and users
