API Docs
========

/
-
* ```GET /``` [home]: Redirect to https://socializr.io

/login
------
* ```GET /login``` [login]: Return login uri's or redirect to https://socializr.io/#/user/profile

/loginfailed
------------
* ```GET /loginfailed``` [loginFailed]: Show error with nice message about the login failing

/logout
-------
* ```GET /logout?_csrf_token={token}``` [logout]: Log the user out

/auth
-----
URI's for login service providers [Google, Facebook and Twitter]

/group
------
* ```GET /group/?limit={20}&offset={0}``` [groupList]: Get all groups where the current user is permitted READ-access
* ```GET /group/{id}``` [groupDetail]: Get the group details if the current user is permitted READ-access

Member requests:
* ```GET /group/{groupId}/board?limit={20}&offset={0}``` [boardList]: Get the boards for a group if the current user is permitted MEMBER-access
* ```POST /group/```: Create a new group and give the current user ADMIN-access
* ```POST /group/{groupId}/board```: Create a new board for the group if the current user is permitted MEMBER-access

Administrator requests:
* ```GET /group/{groupId}/invite/{userId}``` [groupInviteUser]: Invite the user with {userId} for membership of this group
* ```GET /group/{groupId}/block/{userId}``` [groupBlockUser]: Block the user with {userId} for this group
* ```GET /group/{groupId}/promote/{userId}``` [groupPromoteUser]: Make the user with {userId} admin for this group !IRREVERSIBLE!


