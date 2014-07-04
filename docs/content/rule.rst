Rule
====

Rules provide a way to define custom calculation. One rule is defined by its
formula and can be shared in many places. Rules can be used in three different
ways:

#. On a :term:`filter` - :term:`questionnaire` - :term:`part` triplet
#. On a :term:`questionnaire` - :term:`part` pair
#. On a :term:`filter` - :term:`geoname` - :term:`part` triplet


This first application can be used to *override an answer*. For instance if the
answer should not be computed the usual way, then we can customize it to be
anything we want.

The second application is used to compute any arbitrary values within a
questionnaire. Typical usage would be to compute Estimations,
Calculations and Ratios as done in former JMP Excel country files.

Finally, the third application is used much later in the JMP :doc:`computing process<computing>`,
after the regression step. This can be used to define that values should not be
higher than 100%. Conceptually it could be something like: ``Other Unimproved`` = 
``100% - Total improved - Surface water``.


.. note::

    While final results are displayed as percentages between 0 and 100, internally
    they are always computed between 0.00 and 1.00. Therefore formulas must be written
    to work between 0.00 and 1.00. The recommended way to do that, to avoid any
    possible confusion, is to explicitly use the `%` symbol. For example:

        .. code-block:: lua

            =12.5% + 8%


Formula syntax
--------------

The formula syntax is based on the one used in Excel, except that cell references (eg: ``A2``,
``B3``) must be entirely replaced with GIMS specific syntax. The basic structure
of GIMS syntax is enclosed in ``{}``. Within those delimiters are references to
various GIMS objects, according to the possibilities described below. Finally,
as seen above, there are two distinct contexts whose syntaxes cannot be mixed.
The first basic context is before the regression, and the second context is after the
regression.

Where:

* ``F`` = :term:`Filter`
* ``Q`` = :term:`Questionnaire`
* ``P`` = :term:`Part`
* ``R`` = :term:`Rule`
* ``L`` = Level, only two possibilities: absent, or exactly "L#2" to indicate Level 2
* ``Y`` = Year offset

``F``, ``Q`` and ``P``, can have the value ``current`` instead of a specific ID. This means
that the current Filter, Questionnaire or Part should be used, instead of the one selected
by its ID. This syntax should be preferred, whenever possible, to maximize the chances to
share a single rule in many places.


Basic context
^^^^^^^^^^^^^

**Filter value**
    Reference a filter value.

    .. code-block:: lua

        {F#12,Q#34,P#56}
        {F#12,Q#34,P#56,L#2}


**Question label**
    Reference a question label. If the question has no answer, it will return
    ``NULL``. When used with ``ISTEXT()``, it can be used to detect if an answer exists.

    .. code-block:: lua

        {F#12,Q#34}


**Rule value** (Calculations/Estimations/Ratios)
    Reference a rule value. Typically used to reference a Calculation,
    Estimation or Ratio.

    .. code-block:: lua

        {R#12,Q#34,P#56}

    .. warning::

        The referenced rule must exist and be applied to the specified
        questionnaire and part, otherwise computation will fail.


**Population value**
    Reference the population data of the questionnaire\'s country. This is an
    absolute value expressed in number of persons.

    .. code-block:: lua

        {Q#34,P#56}



Regression context
^^^^^^^^^^^^^^^^^^

**Filter value**
    Reference a Filter regression value for a specific part and year. The year
    is defined by the year currently being computed plus a user-defined offset.
    To express "1 year earlier" the offset would be -1, and for "3 years later",
    it would be +3. To stay on the same year, use an offset of 0.

    .. code-block:: lua

        {F#12,P#current,Y0}
        {F#12,P#current,Y-1}
        {F#12,P#current,Y+3}


**List of all filter values**
    Reference a list of available filter values for all questionnaires. The
    result uses the Excel array constant syntax (eg: "{1,2,3}"). This should be used
    with Excel functions such as ``COUNT()`` and ``AVERAGE()``.

    .. code-block:: lua

        {F#12,Q#all}


**Cumulated population**
    Reference the cumulated population for all current questionnaires for the
    specified part.

    .. code-block:: lua

        {Q#all,P#56}

**Current year**
    Reference the year we are currently computing. This may be useful for very
    exceptional edge cases, but should be avoided as much as possible.

    .. code-block:: lua

        {Y}


Both contexts
^^^^^^^^^^^^^

**Value if this rule is ignored**
    Reference the value if computed without this rule. It allows to conditionally
    apply a rule with syntaxes such as ``IF(can_apply_my_rule, compute_some_result, {self})``.

    .. code-block:: lua

        {self}



Examples
^^^^^^^^

An entire formula could be:

.. code-block:: lua

    =IF(ISTEXT({F#12,Q#34}), SUM({F#12,Q#34,P#56}, {R#2,Q#34,P#56}), {R#2,Q#34,P#56})

Or the more re-usable version:

.. code-block:: lua

    =IF(ISTEXT({F#12,Q#current}), SUM({F#12,Q#current,P#current}, {R#2,Q#current,P#current}), {R#2,Q#current,P#current})


Edit and create a rule
----------------------

To edit or create a rule, go to the Administration section and clic on the "Rules" icon.

.. image:: img/administration.png
    :width: 100%
    :alt: Admin section

The name of a rule should be as explicit as possible [1]. The formula is "translated" into a more comprehensible form [2] below the "Formula" field [3].

.. image:: img/rule1.png
    :width: 100%
    :alt: Editing a rule

.. note::

    Since a more user-friendly interface to edit rules will be implemented in the near future, no further details will be provided for the moment.