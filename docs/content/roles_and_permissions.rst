Roles and permissions
=====================

By default, surveys and questionnaires are only visible to their creator.
In order to make this data available to others, they must be explicitly
shared by giving specific roles to these users. The different roles are, in
part, linked to the :doc:`data validation process <data_validation>`.

Roles attributions can be seen and edited in several places via the
:guilabel:`Admin` section. Under the :guilabel:`Users`, you will get an overview
of all the access rights for a given person (see :ref:`specific user page
<user-roles>`). At the level of the :guilabel:`Surveys` as well as the related
questionnaires and :guilabel:`Filter Sets` all users who have an access to these
elements are listed.

There are five different roles, the first three are standard and the last two are special and given to a very limited number of "super-admin" users.

Standard roles
--------------

**Survey editor**
    Can create new surveys, questions and questionnaires.

**Questionnaire reporter**
    Can answer a given questionnaire and once all data entered, can change its
    status from "New" to "Completed".

**Questionnaire validator**
    Can review a completed questionnaire and mark it as "Validated". From this
    point, data can't be modified anymore (unless the status is reverted to
    "New").

.. _special_roles:

Special roles
-------------

**Questionnaire publisher**
    Can make a questionnaire publicly available by setting its status to
    "Published".

**Filter editor**
    Can edit the :ref:`filter set <filterset-roles>` structure.


.. note::

    Need further help? Send an email to who@gimsinitiative.org