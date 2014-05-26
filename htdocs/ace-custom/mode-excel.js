/**
 * Excel syntax, heavily inspired by SQL syntax provided by ACE
 */

ace.define('ace/mode/excel', ['require', 'exports', 'module', 'ace/lib/oop', 'ace/mode/text', 'ace/tokenizer', 'ace/mode/excel_highlight_rules', 'ace/range'], function(require, exports, module) {

    var oop = require("../lib/oop");
    var TextMode = require("./text").Mode;
    var Tokenizer = require("../tokenizer").Tokenizer;
    var ExcelHighlightRules = require("./excel_highlight_rules").ExcelHighlightRules;
    var Range = require("../range").Range;

    var Mode = function() {
        this.HighlightRules = ExcelHighlightRules;
    };
    oop.inherits(Mode, TextMode);

    (function() {

        this.lineCommentStart = "--";

        this.$id = "ace/mode/excel";
    }).call(Mode.prototype);

    exports.Mode = Mode;

});

ace.define('ace/mode/excel_highlight_rules', ['require', 'exports', 'module', 'ace/lib/oop', 'ace/mode/text_highlight_rules'], function(require, exports, module) {

    var oop = require("../lib/oop");
    var TextHighlightRules = require("./text_highlight_rules").TextHighlightRules;

    var ExcelHighlightRules = function() {

        var keywords = (
                "select|insert|update|delete|from|where|and|or|group|by|order|limit|offset|having|as|case|" +
                "when|else|end|type|left|right|join|on|outer|desc|asc"
                );

        var builtinConstants = (
                "true|false|null"
                );

        var builtinFunctions = 'ABS|ACCRINT|ACCRINTM|ACOS|ACOSH|ADDRESS|AMORDEGRC|AMORLINC|AND|AREAS|ASC|ASIN|ASINH|ATAN|ATAN2|ATANH|AVEDEV|AVERAGE|AVERAGEA|' +
                'AVERAGEIF|AVERAGEIFS|BAHTTEXT|BESSELI|BESSELJ|BESSELK|BESSELY|BETADIST|BETAINV|BIN2DEC|BIN2HEX|BIN2OCT|BINOMDIST|CEILING|CELL|' +
                'CHAR|CHIDIST|CHIINV|CHITEST|CHOOSE|CLEAN|CODE|COLUMN|COLUMNS|COMBIN|COMPLEX|CONCATENATE|CONFIDENCE|CONVERT|CORREL|COS|COSH|' +
                'COUNT|COUNTA|COUNTBLANK|COUNTIF|COUNTIFS|COUPDAYBS|COUPDAYS|COUPDAYSNC|COUPNCD|COUPNUM|COUPPCD|COVAR|CRITBINOM|CUBEKPIMEMBER|' +
                'CUBEMEMBER|CUBEMEMBERPROPERTY|CUBERANKEDMEMBER|CUBESET|CUBESETCOUNT|CUBEVALUE|CUMIPMT|CUMPRINC|DATE|DATEDIF|DATEVALUE|DAVERAGE|' +
                'DAY|DAYS360|DB|DCOUNT|DCOUNTA|DDB|DEC2BIN|DEC2HEX|DEC2OCT|DEGREES|DELTA|DEVSQ|DGET|DISC|DMAX|DMIN|DOLLAR|DOLLARDE|DOLLARFR|' +
                'DPRODUCT|DSTDEV|DSTDEVP|DSUM|DURATION|DVAR|DVARP|EDATE|EFFECT|EOMONTH|ERF|ERFC|ERROR.TYPE|EVEN|EXACT|EXP|EXPONDIST|FACT|' +
                'FACTDOUBLE|FALSE|FDIST|FIND|FINDB|FINV|FISHER|FISHERINV|FIXED|FLOOR|FORECAST|FREQUENCY|FTEST|FV|FVSCHEDULE|GAMMADIST|GAMMAINV|' +
                'GAMMALN|GCD|GEOMEAN|GESTEP|GETPIVOTDATA|GROWTH|HARMEAN|HEX2BIN|HEX2DEC|HEX2OCT|HLOOKUP|HOUR|HYPERLINK|HYPGEOMDIST|IF|IFERROR|' +
                'IMABS|IMAGINARY|IMARGUMENT|IMCONJUGATE|IMCOS|IMDIV|IMEXP|IMLN|IMLOG10|IMLOG2|IMPOWER|IMPRODUCT|IMREAL|IMSIN|IMSQRT|IMSUB|IMSUM|' +
                'INDEX|INDIRECT|INFO|INT|INTERCEPT|INTRATE|IPMT|IRR|ISBLANK|ISERR|ISERROR|ISEVEN|ISLOGICAL|ISNA|ISNONTEXT|ISNUMBER|ISODD|ISPMT|' +
                'ISREF|ISTEXT|JIS|KURT|LARGE|LCM|LEFT|LEFTB|LEN|LENB|LINEST|LN|LOG|LOG10|LOGEST|LOGINV|LOGNORMDIST|LOOKUP|LOWER|MATCH|MAX|MAXA|' +
                'MAXIF|MDETERM|MDURATION|MEDIAN|MEDIANIF|MID|MIDB|MIN|MINA|MINIF|MINUTE|MINVERSE|MIRR|MMULT|MOD|MODE|MONTH|MROUND|MULTINOMIAL|' +
                'N|NA|NEGBINOMDIST|NETWORKDAYS|NOMINAL|NORMDIST|NORMINV|NORMSDIST|NORMSINV|NOT|NOW|NPER|NPV|OCT2BIN|OCT2DEC|OCT2HEX|ODD|' +
                'ODDFPRICE|ODDFYIELD|ODDLPRICE|ODDLYIELD|OFFSET|OR|PEARSON|PERCENTILE|PERCENTRANK|PERMUT|PHONETIC|PI|PMT|POISSON|POWER|PPMT|' +
                'PRICE|PRICEDISC|PRICEMAT|PROB|PRODUCT|PROPER|PV|QUARTILE|QUOTIENT|RADIANS|RAND|RANDBETWEEN|RANK|RATE|RECEIVED|REPLACE|REPLACEB|' +
                'REPT|RIGHT|RIGHTB|ROMAN|ROUND|ROUNDDOWN|ROUNDUP|ROW|ROWS|RSQ|RTD|SEARCH|SEARCHB|SECOND|SERIESSUM|SIGN|SIN|SINH|SKEW|SLN|SLOPE|' +
                'SMALL|SQRT|SQRTPI|STANDARDIZE|STDEV|STDEVA|STDEVP|STDEVPA|STEYX|SUBSTITUTE|SUBTOTAL|SUM|SUMIF|SUMIFS|SUMPRODUCT|SUMSQ|SUMX2MY2|' +
                'SUMX2PY2|SUMXMY2|SYD|T|TAN|TANH|TBILLEQ|TBILLPRICE|TBILLYIELD|TDIST|TEXT|TIME|TIMEVALUE|TINV|TODAY|TRANSPOSE|TREND|TRIM|' +
                'TRIMMEAN|TRUE|TRUNC|TTEST|TYPE|UPPER|USDOLLAR|VALUE|VAR|VARA|VARP|VARPA|VDB|VERSION|VLOOKUP|WEEKDAY|WEEKNUM|WEIBULL|WORKDAY|' +
                'XIRR|XNPV|YEAR|YEARFRAC|YIELD|YIELDDISC|YIELDMAT|ZTEST';

        var keywordMapper = this.createKeywordMapper({
            "support.function": builtinFunctions,
            "keyword": keywords,
            "constant.language": builtinConstants
        }, "identifier", true);

        this.$rules = {
            "start": [
                {
                    token: "comment",
                    regex: "--.*$"
                }, {
                    token: "comment",
                    start: "/\\*",
                    end: "\\*/"
                }, {
                    token: "string", // " string
                    regex: '".*?"'
                }, {
                    token: "string", // ' string
                    regex: "'.*?'"
                }, {
                    token: "constant.numeric", // float
                    regex: "[+-]?\\d+(?:(?:\\.\\d*)?(?:[eE][+-]?\\d+)?)?\\b"
                }, {
                    token: keywordMapper,
                    regex: "[a-zA-Z_$][a-zA-Z0-9_$]*\\b"
                }, {
                    token: "keyword.operator",
                    regex: "\\+|\\-|\\/|\\/\\/|%|<@>|@>|<@|&|\\^|~|<|>|<=|=>|==|!=|<>|="
                }, {
                    token: "paren.lparen",
                    regex: "[\\(]"
                }, {
                    token: "paren.rparen",
                    regex: "[\\)]"
                }, {
                    token: "text",
                    regex: "\\s+"
                }
            ]
        };
        this.normalizeRules();
    };

    oop.inherits(ExcelHighlightRules, TextHighlightRules);

    exports.ExcelHighlightRules = ExcelHighlightRules;
});
