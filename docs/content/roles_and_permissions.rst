Roles and permissions
=====================

By default, surveys and questionnaires are only visible to their creator. In
order to make those new data available to other users, they must be explicitly
shared by giving specific roles to users.

Roles attributions are visible in several places in admin pages. First of all,
all the things that a user has access to are listed on that
:ref:`specific user page <user-roles>`. And similarly on Survey, Questionnaires
and Filter Set pages are listed all users who have an access.


Roles
-----

There are several roles available to be given in different context. Each role
must be chosen carefully depending on what the need are.

**Survey editor**
    Can create new surveys, questions and questionnaires.

**Questionnaire reporter**
    Can answer a given questionnaire and mark it is as complete.

**Questionnaire validator**
    Can review a completed questionnaire and mark it as validated. From then
    data cannot be modified anymore.

**Questionnaire publisher**
    Can make a questionnaire publicly available. Very few users have this role.

**Filter editor**
    Can edit the filter structure. Very few users have this role.


Publishing
----------

To make data publicly available to anybody, including non-logged in users, the
questionnaires status must be changed to "Published". Once published data cannot
be modified anymore.

Given the important impact it may have, publishing a questionnaire is only
possible for very few users, via the role "Questionnaire publisher".
