/**
 * Set the default options for pie
 */
defaultPlotOptions.pie = merge(defaultSeriesOptions, {
	borderColor: '#FFFFFF',
	borderWidth: 1,
	center: [null, null],
	clip: false,
	colorByPoint: true, // always true for pies
	dataLabels: {
		// align: null,
		// connectorWidth: 1,
		// connectorColor: point.color,
		// connectorPadding: 5,
		distance: 30,
		enabled: true,
		formatter: function () {
			return this.point.name;
		}
		// softConnector: true,
		//y: 0
	},
	ignoreHiddenPoint: true,
	//innerSize: 0,
	legendType: 'point',
	marker: null, // point options are specified in the base options
	size: null,
	showInLegend: false,
	slicedOffset: 10,
	states: {
		hover: {
			brightness: 0.1,
			shadow: false
		}
	},
	stickyTracking: false,
	tooltip: {
		followPointer: true
	}
});

/**
 * Extended point object for pies
 */
var PiePoint = extendClass(Point, {
	/**
	 * Initiate the pie slice
	 */
	init: function () {

		Point.prototype.init.apply(this, arguments);

		var point = this,
			toggleSlice;

		// Disallow negative values (#1530)
		if (point.y < 0) {
			point.y = null;
		}

		//visible: options.visible !== false,
		extend(point, {
			visible: point.visible !== false,
			name: pick(point.name, 'Slice')
		});

		// add event listener for select
		toggleSlice = function (e) {
			point.slice(e.type === 'select');
		};
		addEvent(point, 'select', toggleSlice);
		addEvent(point, 'unselect', toggleSlice);

		return point;
	},

	/**
	 * Toggle the visibility of the pie slice
	 * @param {Boolean} vis Whether to show the slice or not. If undefined, the
	 *    visibility is toggled
	 */
	setVisible: function (vis) {
		var point = this,
			series = point.series,
			chart = series.chart,
			method;

		// if called without an argument, toggle visibility
		point.visible = point.options.visible = vis = vis === UNDEFINED ? !point.visible : vis;
		series.options.data[inArray(point, series.data)] = point.options; // update userOptions.data
		
		method = vis ? 'show' : 'hide';

		// Show and hide associated elements
		each(['graphic', 'dataLabel', 'connector', 'shadowGroup'], function (key) {
			if (point[key]) {
				point[key][method]();
			}
		});

		if (point.legendItem) {
			chart.legend.colorizeItem(point, vis);
		}
		
		// Handle ignore hidden slices
		if (!series.isDirty && series.options.ignoreHiddenPoint) {
			series.isDirty = true;
			chart.redraw();
		}
	},

	/**
	 * Set or toggle whether the slice is cut out from the pie
	 * @param {Boolean} sliced When undefined, the slice state is toggled
	 * @param {Boolean} redraw Whether to redraw the chart. True by default.
	 */
	slice: function (sliced, redraw, animation) {
		var point = this,
			series = point.series,
			chart = series.chart,
			translation;

		setAnimation(animation, chart);

		// redraw is true by default
		redraw = pick(redraw, true);

		// if called without an argument, toggle
		point.sliced = point.options.sliced = sliced = defined(sliced) ? sliced : !point.sliced;
		series.options.data[inArray(point, series.data)] = point.options; // update userOptions.data

		translation = sliced ? point.slicedTranslation : {
			translateX: 0,
			translateY: 0
		};

		point.graphic.animate(translation);
		
		if (point.shadowGroup) {
			point.shadowGroup.animate(translation);
		}

	}
});

/**
 * The Pie series class
 */
var PieSeries = {
	type: 'pie',
	isCartesian: false,
	pointClass: PiePoint,
	requireSorting: false,
	noSharedTooltip: true,
	trackerGroups: ['group', 'dataLabelsGroup'],
	axisTypes: [],
	pointAttrToOptions: { // mapping between SVG attributes and the corresponding options
		stroke: 'borderColor',
		'stroke-width': 'borderWidth',
		fill: 'color'
	},

	/**
	 * Pies have one color each point
	 */
	getColor: noop,

	/**
	 * Animate the pies in
	 */
	animate: function (init) {
		var series = this,
			points = series.points,
			startAngleRad = series.startAngleRad;

		if (!init) {
			each(points, function (point) {
				var graphic = point.graphic,
					args = point.shapeArgs;

				if (graphic) {
					// start values
					graphic.attr({
						r: series.center[3] / 2, // animate from inner radius (#779)
						start: startAngleRad,
						end: startAngleRad
					});

					// animate
					graphic.animate({
						r: args.r,
						start: args.start,
						end: args.end
					}, series.options.animation);
				}
			});

			// delete this function to allow it only once
			series.animate = null;
		}
	},

	/**
	 * Extend the basic setData method by running processData and generatePoints immediately,
	 * in order to access the points from the legend.
	 */
	setData: function (data, redraw) {
		Series.prototype.setData.call(this, data, false);
		this.processData();
		this.generatePoints();
		if (pick(redraw, true)) {
			this.chart.redraw();
		} 
	},

	/**
	 * Extend the generatePoints method by adding total and percentage properties to each point
	 */
	generatePoints: function () {
		var i,
			total = 0,
			points,
			len,
			point,
			ignoreHiddenPoint = this.options.ignoreHiddenPoint;

		Series.prototype.generatePoints.call(this);

		// Populate local vars
		points = this.points;
		len = points.length;
		
		// Get the total sum
		for (i = 0; i < len; i++) {
			point = points[i];
			total += (ignoreHiddenPoint && !point.visible) ? 0 : point.y;
		}
		this.total = total;

		// Set each point's properties
		for (i = 0; i < len; i++) {
			point = points[i];
			point.percentage = total > 0 ? (point.y / total) * 100 : 0;
			point.total = total;
		}
		
	},
	
	/**
	 * Do translation for pie slices
	 */
	translate: function (positions) {
		this.generatePoints();
		
		var series = this,
			cumulative = 0,
			precision = 1000, // issue #172
			options = series.options,
			slicedOffset = options.slicedOffset,
			connectorOffset = slicedOffset + options.borderWidth,
			start,
			end,
			angle,
			startAngle = options.startAngle || 0,
			startAngleRad = series.startAngleRad = mathPI / 180 * (startAngle - 90),
			endAngleRad = series.endAngleRad = mathPI / 180 * ((options.endAngle || (startAngle + 360)) - 90),
			circ = endAngleRad - startAngleRad, //2 * mathPI,
			points = series.points,
			radiusX, // the x component of the radius vector for a given point
			radiusY,
			labelDistance = options.dataLabels.distance,
			ignoreHiddenPoint = options.ignoreHiddenPoint,
			i,
			len = points.length,
			point;

		// Get positions - either an integer or a percentage string must be given.
		// If positions are passed as a parameter, we're in a recursive loop for adjusting
		// space for data labels.
		if (!positions) {
			series.center = positions = series.getCenter();
		}

		// utility for getting the x value from a given y, used for anticollision logic in data labels
		series.getX = function (y, left) {

			angle = math.asin((y - positions[1]) / (positions[2] / 2 + labelDistance));

			return positions[0] +
				(left ? -1 : 1) *
				(mathCos(angle) * (positions[2] / 2 + labelDistance));
		};

		// Calculate the geometry for each point
		for (i = 0; i < len; i++) {
			
			point = points[i];
			
			// set start and end angle
			start = startAngleRad + (cumulative * circ);
			if (!ignoreHiddenPoint || point.visible) {
				cumulative += point.percentage / 100;
			}
			end = startAngleRad + (cumulative * circ);

			// set the shape
			point.shapeType = 'arc';
			point.shapeArgs = {
				x: positions[0],
				y: positions[1],
				r: positions[2] / 2,
				innerR: positions[3] / 2,
				start: mathRound(start * precision) / precision,
				end: mathRound(end * precision) / precision
			};

			// center for the sliced out slice
			angle = (end + start) / 2;
			if (angle > 0.75 * circ) {
				angle -= 2 * mathPI;
			}
			point.slicedTranslation = {
				translateX: mathRound(mathCos(angle) * slicedOffset),
				translateY: mathRound(mathSin(angle) * slicedOffset)
			};

			// set the anchor point for tooltips
			radiusX = mathCos(angle) * positions[2] / 2;
			radiusY = mathSin(angle) * positions[2] / 2;
			point.tooltipPos = [
				positions[0] + radiusX * 0.7,
				positions[1] + radiusY * 0.7
			];
			
			point.half = angle < -mathPI / 2 || angle > mathPI / 2 ? 1 : 0;
			point.angle = angle;

			// set the anchor point for data labels
			connectorOffset = mathMin(connectorOffset, labelDistance / 2); // #1678
			point.labelPos = [
				positions[0] + radiusX + mathCos(angle) * labelDistance, // first break of connector
				positions[1] + radiusY + mathSin(angle) * labelDistance, // a/a
				positions[0] + radiusX + mathCos(angle) * connectorOffset, // second break, right outside pie
				positions[1] + radiusY + mathSin(angle) * connectorOffset, // a/a
				positions[0] + radiusX, // landing point for connector
				positions[1] + radiusY, // a/a
				labelDistance < 0 ? // alignment
					'center' :
					point.half ? 'right' : 'left', // alignment
				angle // center angle
			];

		}
	},

	setTooltipPoints: noop,
	drawGraph: null,

	/**
	 * Draw the data points
	 */
	drawPoints: function () {
		var series = this,
			chart = series.chart,
			renderer = chart.renderer,
			groupTranslation,
			//center,
			graphic,
			//group,
			shadow = series.options.shadow,
			shadowGroup,
			shapeArgs;

		if (shadow && !series.shadowGroup) {
			series.shadowGroup = renderer.g('shadow')
				.add(series.group);
		}

		// draw the slices
		each(series.points, function (point) {
			graphic = point.graphic;
			shapeArgs = point.shapeArgs;
			shadowGroup = point.shadowGroup;

			// put the shadow behind all points
			if (shadow && !shadowGroup) {
				shadowGroup = point.shadowGroup = renderer.g('shadow')
					.add(series.shadowGroup);
			}

			// if the point is sliced, use special translation, else use plot area traslation
			groupTranslation = point.sliced ? point.slicedTranslation : {
				translateX: 0,
				translateY: 0
			};

			//group.translate(groupTranslation[0], groupTranslation[1]);
			if (shadowGroup) {
				shadowGroup.attr(groupTranslation);
			}

			// draw the slice
			if (graphic) {
				graphic.animate(extend(shapeArgs, groupTranslation));
			} else {
				point.graphic = graphic = renderer.arc(shapeArgs)
					.setRadialReference(series.center)
					.attr(
						point.pointAttr[point.selected ? SELECT_STATE : NORMAL_STATE]
					)
					.attr({ 'stroke-linejoin': 'round' })
					.attr(groupTranslation)
					.add(series.group)
					.shadow(shadow, shadowGroup);	
			}

			// detect point specific visibility (#2430)
			if (point.visible !== undefined) {
				point.setVisible(point.visible);
			}

		});

	},

	/**
	 * Utility for sorting data labels
	 */
	sortByAngle: function (points, sign) {
		points.sort(function (a, b) {
			return a.angle !== undefined && (b.angle - a.angle) * sign;
		});
	},		

	/**
	 * Draw point specific tracker objects. Inherit directly from column series.
	 */
	drawTracker: TrackerMixin.drawTrackerPoint,

	/**
	 * Use a simple symbol from LegendSymbolMixin
	 */
	drawLegendSymbol: LegendSymbolMixin.drawRectangle,

	/**
	 * Use the getCenter method from drawLegendSymbol
	 */
	getCenter: CenteredSeriesMixin.getCenter,

	/**
	 * Pies don't have point marker symbols
	 */
	getSymbol: noop

};
PieSeries = extendClass(Series, PieSeries);
seriesTypes.pie = PieSeries;

