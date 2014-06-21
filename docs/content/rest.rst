REST API
========

GIMS uses a `REST API <http://en.wikipedia.org/wiki/Representational_state_transfer>`_
which is available on the URL `/api` and allow developers to manipulate GIMS data.

The API answers to the following HTTP methods:

======  =====================================  ============
Verb    Action                                 Success code
======  =====================================  ============
GET     Get an item, or a collection of items  200
PUT     Update an item                         201
POST    Create an item                         201
DELETE  Delete an item                         204
======  =====================================  ============

Possible error codes are:

401
    Unauthorized, the request should be repeated after successfully logged in

403
    Action is denied because of lack of permission, or the submitted data are invalid.
404
    Item could not be found

Fields
------

The optional parameter `?fields=children` allow ask for additional fields to be
be returned. By default the API return the strict minimum to be as fast as possible.
But it is possible to get a very complex object graph if needed.

Get a survey with its minimal fields:

    .. code::

        /api/survey/1

Get the same survey, but with additional fields:

    .. code::

        /api/survey/1?fields=comments

And even more fields, with sub-items:

    .. code::

        /api/survey/1?fields=comments,metadata,questionnaires.geoname

    .. note::

        `metadata` is a special field allowing to return fields common to all
        item types. It will be expanded to `dateCreated,dateModified,creator,modifier`.


Recursivity
^^^^^^^^^^^

It is possible to ask for items, and their fields, recursively by using the
special field `__recursive`.

The following example will return all filters and all its children, and its
children's children and so on:

    .. code::

        /api/filter/1?fields=children.__recursive

Specified fields, if any, are also treated recursively. So the following will
return summands for each children.

    .. code::

        /api/filter/1?fields=children.__recursive,summands


Searching
---------

The optional `?q=my search terms` parameters allow to search, or filter, items.
The fields searched for are dependent on the type of item, but in most case it
will search in code and name field.


Pagination
----------

Collection of items are returned in a paginated form. Those optional parameters
allow to control what is returned:

`?perPage`
    The maximum number of items per page.

`?page`
    The page number to be returned. Starting at 1.

By default, it will return the 25 first items of the first page.


Permission and security
-----------------------

A field `permissions` exists for all item types, and show the permission on the
item.

    .. code::

        /api/filter/1?fields=permissions

While this is convenient for single item or small collections, when it comes to
large collection it is advised to filter them when loading them. This can be
achieved with `?permission` parameter:

    .. code::

        /api/questionnaire?permission=update

This will return only questionnaires on which the user has `update` permission.


Validation
-----------------------

When specifying `?validate=true` with PUT or POST method, the database will never
be modified, but the submitted data will be validated and appropriate validation
messages will be returned in case of failure (as well as a 403 code).

A successful validation will return the code 200 and the item as it would be if
the operation actually happened.
