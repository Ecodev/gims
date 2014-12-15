JMP data reconciliation process
===============================

*Provider-based* and *user-based* monitoring approaches measure different,
yet complementary, things:

* *outputs* (what infrastructures are available to the population) is
  typically measured by the :term:`NSA`. Numerous countries are developing
  Sector Information Management Systems (SIMS) to this effect.
* *outcomes* (what infrastructures people are actually using) is measured by the
  :term:`NSO` and other organisations via household surveys and censuses. This
  information makes up the basic and historical corpus of the :term:`JMP`
  which is now managed within GIMS.

GIMS enables to handle these two approaches and compare results so as to
identify existing problems that can be related to different definitions
of improved facilities, different methodologies for estimating coverage,
or different definitions of urban/rural, etc.

Editing and comparing JMP and NSO data
--------------------------------------

Also NSO and JMP both measure *outcomes*, data may differ due to different
definitions of what is considered improved / unimproved both for water and sanitation.

NSO users can visualize the impact of such modifications via the Browse /
Charts section by ignoring certain filters and/or questionnaires and seeing
the impact of this adaptation on the calculated trend line. In the example
below, data under "Public tap, standpipe" has been ignored for all
questionnaires [1] (see the :ref:`JMPgraphAnalysis` section for basic notions).

.. note::

    Piped onto premises data points and trend lines have been hidden by clicking
    on the elements in the legend [3].

.. image:: img/data_reconciliation1.png
    :width: 100%
    :alt: Data reconciliation process

NSO users can easily add one or several data sets, if needed, by clicking on the
"Edit in JMP questionnaires" button at the top [2].

.. warning::

    These new questionnaires will only be visible to their author and the
    users to whom he will give the access rights (see how to give access rights
    to :doc:`user`)!

Click on :guilabel:`Add new questionnaire` [1], enter a relevant survey code and
the year [2], enter the percentages [3] (don't forget to enter values for all
parts: urban, rural and total !) and click on the blue :guilabel:`Save new
elements` button [4]. Repeat this process as many times as necessary. To view
the impact of these new data points in the chart, click on the :guilabel:`View
in chart` button [5].

.. image:: img/data_reconciliation1b.png
    :width: 100%
    :alt: Editing data in the JMP contribute table

.. note::

    If JMP staff decides to integrate this new data set into the officially
    approved data, they can do so by changing the status of the questionnaire
    from "new" to "published".

Having added one or several new datasets (in the example below "MHT95" and "MHT10"); ignored certain filters and eventually also certain questionnaires, the resulting data points and trend line will provide the National Statistics Office's version [1]. The difference with the original JMP data points and trend line in shaded color [2] are clearly visible.

.. note::

    If you wish to share your graph with someone else, simply copy the URL
    [3] and send it to him. Don't forget to give him the access rights to see
    your new data points !

.. image:: img/data_reconciliation2.png
    :width: 100%
    :alt: Data reconciliation process


Editing and comparing JMP/NSO and NSA data
------------------------------------------

JMP/NSO data will most certainly differ from :term:`NSA` data. One important
reason is that the estimated number of persons having access to the facilities
according to NSA data often doesn't match with effective use (i.e. what
JMP/NSO measure).

To add NSA sector data, simply click on the :guilabel:`Edit NSA questionnaires`
button.

.. image:: img/data_reconciliation3.png
    :width: 100%
    :alt: NSA data entry

.. note::

    To review how to edit NSA data, see the :ref:`DI NSA` section.

In the example below, we assume NSA have detailed data regarding piped water into and outside houses, the later being equivalent to the "piped water to yard/plot" filter.

.. note::

    For these sector data equipments, we recommend you append "SD" to the equipment label so as to distinguish easily NSA data in the chart.

.. image:: img/data_reconciliation4.png
    :width: 100%
    :alt: NSA data entry

Once the NSA sector data has been created, you can display it on the graph so as to compare data points and corresponding trends lines with official JMP values.

To ensure the comparisons performed are meaningful, it is best that you only display equivalent filters [1]. In the example below, we have displayed the JMP filter "Piped water into dwelling" (in red) and the sector data "Piped water into houses (SD)" (in blue).


Projection
----------

To discover by how many persons per equipment the NSA estimations need to be corrected, click on the "Projection" tab [2] and select the parameters to be used:

* **Filter for projection** [3]: select the trend line you wish to move. In
  the example below the NSA data (blue line)
* **Target of projection** [4]: select the trend line you wish to match. In our
  example the JMP data (red line)
* **Compute value after projection** [5]: select the parameter that should be
  calculated, typically the number of persons per equipment.

…and then click on the :guilabel:`Apply` button [6].

.. image:: img/data_reconciliation5.png
    :width: 100%
    :alt: Comparable JMP and NSA data plotted

The NSA trend line drops down in our example to adjust to the JMP data.

.. image:: img/data_reconciliation6.png
    :width: 100%
    :alt: Trend line adjustement

To discover by how many persons the estimation should be adjusted, click on the "Differences" tab. In our example, we see that the initial NSA estimation of 10 persons per equipment is close to the double of effective numbers (between 4.5 and 5.5 persons).

.. image:: img/data_reconciliation7.png
    :width: 100%
    :alt: Projection tab for graphical data reconciliation

Another explanation to the mismatch could be linked to the fact that not all equipments are still operational. To adjust the numbers, click on the :guilabel:`Edit NSA questionnaires` button and in the table, click on the question mark icon [1] to show the contextual menu. Slide the value from 100% to whatever percentage is correct (or the most probable) [2].

.. image:: img/data_reconciliation8.png
    :width: 100%
    :alt: Adjustment of quality slider percentage

By clicking on the :guilabel:`View in chart` button we can see that the trend lines match pretty well with our 50% of equipments operational.

.. image:: img/data_reconciliation9.png
    :width: 100%
    :alt: Trend line match after quality adjustment

.. note::

    As in most situations, the explanation is probably a mix of both a certain
    percentage of equipments not in use / out of order as well as an error in
    the estimation of the number of people served per equipment.

.. note::

    Need further help? Send an email to who@gimsinitiative.org