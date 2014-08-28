Computing
=========

Computing is a complex process that can be split in 4 steps. Here is an
overview of that process:

.. _my-computing-image:

.. image:: img/computing.*

This can be briefly summarized as:

#. :ref:`Compute all filters for one questionnaire <step1>`
#. :ref:`Compute all questionnaires <step2>`
#. :ref:`Compute all years <step3>`
#. :ref:`Fine tune the final result <step4>`

While the default computing behavior should satisfy a majority of case, the
user can choose to customize it in three of those steps (via rules or summands).
Those optional customization points are shown in orange on the illustration.


.. _step1:

Step 1 - Completing filter values
---------------------------------

The first step of computation is to complete filter values, as much as possible,
based on the value entered by the user for other filters. So if the user entered
data for ``House connections`` and ``Public tap``, we are able to sum those
values to get a computed value for the parent filter ``Tap water``.

There are three distinct ways to compute a value for a filter. The first
available possibility will be used, and the rest will be discarded:

#. A custom :term:`rule` defined by user
#. A custom list of :term:`summands<summand>` to be summed
#. The natural children of the filter to be summed

The last case is by far the most common one. It means a parent filter is the sum
of its children. This is used for the vast majority of filters and nothing
special needs to be done, except build the correct filter hierarchy once and
for all.

The two other possibilities are the first way to customize GIMS computation. A
simple customization for a filter **across all questionnaires** can be done via
the summands. This is typically useful to create filters that act as a summary
of other transversal filters, rather than the standard hierarchical behavior.

And finally, rules allow the user to create very specific formula for **a specific
questionnaire only**. Or share a rule for a few specific questionnaires only.

The result of this computation step is the equivalent of sheets *Tables_W* and
*Tables_S* in former Excel country files. In GIMS, it can be viewed on
:menuselection:`Browse --> Table --> Filter` page.


.. _step2:

Step 2 - Computing questionnaires values
----------------------------------------

The second step use the previous results from all questionnaires and compute
some statistics. Those statistics are for internal use only and are not available
to end-user.

However it is in that step that the user can use rules of level 2. Those rules of
level 2 are typically used to ignore values. While some values were interesting
in the previous step, we can decide that we are no longer interested in them for
this and any following computing steps.

Rules of level 2 are also the first opportunity to say ``Total = Urban + Rural``.
Again because we wanted to keep the raw number for ``Total`` in previous step,
but from now on we want to change the way ``Total`` is computed.

The result of this computation step is the equivalent of sheets *GraphData_W*
and *GraphData_S* in former Excel country files. In GIMS, it can be viewed on
:menuselection:`Browse --> Table --> Questionnaire` page, or as the points on
the chart on :menuselection:`Browse --> Chart` page.


.. _step3:

Step 3 - Computing regression
-----------------------------

So far we computed values on a per filter and then per questionnaire basis. But
the final goal is to compute on a per year basis. This is done in this step, via
regression.

According to statistics from previous step, we will use Excel function
``AVERAGE()`` if we don't have much data, or ``FORECAST()`` if we have enough
significant data. More details about edge cases are available in GIMS
`source code <https://github.com/Ecodev/gims/blob/master/module/Application/src/Application/Service/Calculator/Calculator.php#L231>`__.

The result of this computation step is the equivalent of the tables on the right
of sheet *Estimates* in former Excel country files. In GIMS, it is not available,
since it is only an intermediary result.


.. _step4:

Step 4 - Flattening regression
------------------------------

Finally the last step is to *standardize* the regression. First we will enforce
all values between 0% and 100% (internally 0.00 and 1.00). Then we will try to
project in the past and in the future, if the data are good or bad enough. This
is what cause the horizontal parts of trend lines in chart. See
`source code <https://github.com/Ecodev/gims/blob/master/module/Application/src/Application/Service/Calculator/Calculator.php#L126>`__ for details.

This is also the last opportunity to use rules to customize results. In this
case the rules are applied **per country**. They can be used to say, again,
``Total = Urban + Rural``. Or they can be used to do complementary computation
such as ``Other Improved = Total improved - Piped onto premises``.

The final result of this computation step is the equivalent of the tables on the
left of sheet *Estimates* in former Excel country files. In GIMS, it can be viewed
on :menuselection:`Browse --> Table --> Country` page, or as the trend lines on
the chart on :menuselection:`Browse --> Chart` page.


.. note::

    In this section we mentioned two kind of applications for rules: for filter
    (level 1 and 2) and for country. There is a third kind, for questionnaire,
    that may be useful. See the :doc:`full explanation<rule>`.
