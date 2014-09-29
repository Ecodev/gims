Short technical presentation
============================

GIMS is a modern, robust yet flexible **open source** system that enables
users to collect in a structured and decentralized manner important amounts
of data that can then be processed within GIMS and/or exported in Excel
format for external analysis.

A flexible **filtering system** helps group and compare data sets at different
**spatial**, **temporal** and **thematic** levels thus enabling cross-data
queries and analysis that can be viewed in the form of **tables**, **charts**
and **maps** (not yet operational).

GIMS empowers all actors, be they Governments or NGO’s to help them focus
and optimize their efforts where it is most needed and, at the level of the
beneficiaries, enables the population to highlight and question official
information that may not match what they observe and experience on the field
thus providing valuable feedback.

Server architecture
^^^^^^^^^^^^^^^^^^^

On the server side, data is stored in a **PostgreSQL database** with **PostGIS
extension**, one of the leading projects for geospatial data. The application
itself is developed in **PHP**, a very widely used programming language for
web servers. This gives us access to several well-known and robust libraries.

To communicate with the database, we use `Doctrine 2
<http://www.doctrine-project.org>`_. This library is based on proven concepts,
first imagined in Java  and then ported to several other programming languages,
such as PHP in the case of Doctrine. This simplifies standard usages and
maintenance of the database, while still allowing for very advanced use-cases
via native queries.

Excel files support and Excel formulas computation are made possible via
`PHPExcel <https://github.com/PHPOffice/PHPExcel>`_ which is the most complete
open source solution to create/read Excel files without any dependencies on
any Microsoft software. Not only that, but its own implementation of Excel
functions proved to be invaluable to reproduce complex JMP computing rules.

To tie everything together on the server side, we use **Zend Framework
2**. Developed by the very same company that is behind PHP itself, Zend
Framework 1 was the first framework to adopt modern development paradigms
(unit testing, strict coding style, etc.). With version 2.0, the project
has become mature.

Finally, data is made available to clients (browsers) via a `REST API
<https://en.wikipedia.org/wiki/Representational_state_transfer>`_ which is
the de-facto standard for any modern web application. This provides optimal
flexibility, decoupling server and client and providing a mean for hypothetical
third-party developers to build their own client (different graphs, tables,
export or even smartphone applications).

Client architecture
^^^^^^^^^^^^^^^^^^^

`AngularJS <http://angularjs.org/>`_ is the framework used on the client
side, at the level of the web browser. It is very actively developed
and used by Google. This JavaScript framework introduced a new way
of working on the client side and is definitely future oriented. It
was voted in 2013 as `one of the three most exciting technologies
<http://blog.stackoverflow.com/2014/02/2013-stack-overflow-user-survey-results/>`_.

On top of AngularJS, we use several other libraries, such as `Restangular
<https://github.com/mgonto/restangular>`_ for easier REST API usage, and
`Angular UI <http://angular-ui.github.io/>`_  components for grids. For charts,
`Highcharts <http://www.highcharts.com/>`_ is by far the most complete and
mature solution (across all chart types).

Building and testing tools
^^^^^^^^^^^^^^^^^^^^^^^^^^

Considering the complexity and increasing amount of code that makes up the
system, we use several additional tools to help with the development process
itself. To optimize the efficiency of the application, we use preprocessors
for JavaScript and CSS (`UglifyJs <https://github.com/mishoo/UglifyJS>`_ and
`Compass <http://compass-style.org/>`_), and to ensure the technical quality
of the application, we write unit tests with **PHPUnit** and **Karma**
which are then run by `Travis CI <https://travis-ci.org/Ecodev/gims>`_
automatically. In addition to that, the code quality is checked with
`Scrutinizer CI <https://scrutinizer-ci.com/g/Ecodev/gims/>`_.

For an overview of the architecture, see the diagram below.

.. image:: img/architecture.*
    :width: 100%
    :alt: GIMS architecture diagram
