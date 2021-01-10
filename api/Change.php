<?php
/**
 * @author Matt Lisiak
 * Change API
 *
 * Returns monetary change denominations and amounts.
 */

namespace API;

// require our api configuration variables
require_once(realpath(dirname(__FILE__) . '/../config.php'));

require_once(realpath(dirname(__FILE__) . '/../Database.php'));

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Change
{
    private $_app;

    public function __construct($app)
    {
        $this->_app = $app;
    }

    /**
     * @api {get} /api/change/{owed}/{paid}[/{lang}])  Returns change for given values by denominations
     * @apiVersion 1.0.0
     * @apiName GetChange
     * @apiGroup Change
     *
     * @apiParam {Number|String} owed   Amount owed
     * @apiParam {Number|String} paid   Amount customer paid
     * @apiParam {String} [lang]        Language to get change for (defaults to LANGUAGE in config.php)
     *
     * @apiSuccess {String} owed    Amount owed
     * @apiSuccess {String} paid    Amount customer paid
     * @apiSuccess {String} lang    Language of returned denominations
     * @apiSuccess {String} change  Change owed by named denominations
     * @apiSuccess {Number} value   Change owed as a numerical value
     *
     * @apiSuccessExample Success-Response:
     *
     *     HTTP/1.1 200 OK
     *     {
     *          "owed": "2.12",
     *          "paid": "3.00",
     *          "lang": "en-US",
     *          "change": "3 quarters,1 dime,3 pennies",
     *          "value": "0.88"
     *     }
     *
     * @apiError InvalidParameter
     *     Invalid parameter(s) specified in request. 400
     *
     * @apiErrorExample 400 Response:
     *
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "error": "InvalidParameter",
     *         "code": 3000
     *         "message": "Invalid Parameter"
     *     }
     *
     * @apiError NotFound
     *     Requested resource not found. 404
     *
     * @apiErrorExample 404 Response:
     *
     *     HTTP/1.1 404 Not Found
     *     {
     *         "error": "NotFound",
     *         "code": 3000
     *         "message": "Denominations not found for given language."
     *     }
     *
     * @apiError DatabaseError
     *     Error retrieving from database. 500
     *
     * @apiErrorExample 500 Response:
     *
     *     HTTP/1.1 500 Internal Server Error
     *     {
     *         "error": "DatabaseError",
     *         "code": 3000
     *         "message": "Error retrieving from database."
     *     }
     */
    public function getChange(Request $request, Response $response, $args)
    {
        $owed = isset($args['owed']) ? $args['owed'] : null;
        $paid = isset($args['paid']) ? $args['paid'] : null;
        $lang = isset($args['lang']) ? $args['lang'] : null;

        if (!is_numeric($owed) || $owed < 0) {
            // Owed not valid
            $errorResponse = $response->withJson(array(
                "error" => "InvalidParameter",
                "code" => 3001,
                "message" => "Invalid Parameter: Owed is not a valid number."
            ));
            $errorResponse = $errorResponse->withStatus(400);
            return $errorResponse;
        }

        if (!is_numeric($paid) || $paid < 0) {
            // Paid not valid
            $errorResponse = $response->withJson(array(
                "error" => "InvalidParameter",
                "code" => 3002,
                "message" => "Invalid Parameter: Paid is not a valid number."
            ));
            $errorResponse = $errorResponse->withStatus(400);
            return $errorResponse;
        }

        if (floatval($paid) < floatval($owed)) {
            // Paid amount is less than Owed
            $errorResponse = $response->withJson(array(
                "error" => "InvalidParameter",
                "code" => 3003,
                "message" => "Invalid Parameter: Insufficient payment."
            ));
            $errorResponse = $errorResponse->withStatus(400);
            return $errorResponse;
        }

        if (is_null($lang)) {
            $lang = LANGUAGE;
        }

        try {
            $denoms = Change::getDenominationDatabase($lang);
        } catch (\Exception $e) {
            $errorResponse = $response->withJson(array(
                "error" => "DatabaseError",
                "code" => 3004,
                "message" => "Error retrieving from database."
            ));
            $errorResponse = $errorResponse->withStatus(500);
            return $errorResponse;
        }

        if (empty($denoms)) {
            // denominations not in database
            $errorResponse = $response->withJson(array(
                "error" => "NotFound",
                "code" => 3005,
                "message" => "Denominations not found for language $lang."
            ));
            $errorResponse = $errorResponse->withStatus(404);
            return $errorResponse;
        }

        $values = array();
        $change_denoms = array();
        $change_value_list = array();
        $change_total = 0.0;
        $change_value = floatval($paid) - floatval($owed);

        foreach ($denoms as $denom) {
            $values[$denom["value"]] = $denom;
        }

        if (USE_DIVISOR_RANDOMIZER && (($owed*100) % (DIVISOR*100)) === 0) {
            // Special case where divisor option is enabled and change value is evenly divisible

            ksort($values); // Sort array by key in ascending order (smallest first)
            $value_keys = array_keys($values);

            while ($change_total < $change_value &&
                round(($change_value - $change_total), 2) >= $value_keys[0] // prevent infinite loop
            ) {
                // get a random denomination value
                $rand_value = $value_keys[rand(0, count($value_keys)-1)];

                // add the random value to the change list if it is smalleror equal
                // to the remainder (round to fix floating point precision errors)
                if ($rand_value <= round(($change_value - $change_total), 2)) {
                    $change_value_list[] = $rand_value;
                    $change_total += $rand_value;
                }
                // repeat until there is no more change to be given out, or
                // an (unlikely) infinite loop scenario occurs where the change
                // owed is not evenly divisible by the smallest coin denomination
                // (i.e., pennies are abolished but the amount owed is $1.02)
            }
        } else {
            // Normal operation

            // Sort array by key in ascending order (smallest first)
            ksort($values);
            $value_keys = array_keys($values);

            // Then sort array by key in descending order (largest first)
            krsort($values);

            while ($change_total < $change_value &&
                round(($change_value - $change_total), 2) >= $value_keys[0] // prevent infinite loop
            ) {
                foreach ($values as $value => $value_data) {
                    // go through each denomination until the largest value
                    // that fits in the change remainder is found
                    // (round to fix floating point precision errors)
                    if ($value <= round(($change_value - $change_total), 2)) {
                        $change_value_list[] = $value;
                        $change_total += $value;
                        break;  // value found, break out of foreach
                    }
                }
                // repeat until there is no more change to be given out, or
                // an (unlikely) infinite loop scenario occurs where the change
                // owed is not evenly divisible by the smallest coin denomination
                // (i.e., pennies are abolished but the amount owed is $1.02)
            }
        }

        // count distinct values
        $value_count = array_count_values($change_value_list);

        // sort by largest value first
        krsort($value_count);

        foreach ($value_count as $value => $count) {
            $denom_phrase = $count . ' ';
            if ($count !== 1) {
                $denom_phrase .= $values[$value]["plural"];
            } else {
                $denom_phrase .= $values[$value]["name"];
            }

            $change_denoms[] = $denom_phrase;
        }

        $change = implode(SEPARATOR, $change_denoms);

        $output = array(
            "owed" => number_format($owed, 2, DECIMAL, ''),
            "paid" => number_format($paid, 2, DECIMAL, ''),
            "lang" => $lang,
            "change" => $change,
            "value" => number_format($change_value, 2, DECIMAL, '')
        );

        $response = $response->withJson($output, null, JSON_UNESCAPED_UNICODE);
        return $response;
    }

    /**
     * @api {get} /api/change/denomination[/{lang}]  Returns currency denominations
     * @apiVersion 1.0.0
     * @apiName GetChangeDenomination
     * @apiGroup Change
     *
     * @apiParam {String} [lang]    Language to get denominations for (defaults to LANGUAGE in config.php)
     *
     * @apiSuccess {String} lang    Language code
     * @apiSuccess {String} name    Singular denomination name
     * @apiSuccess {String} plural  Plural denomination name
     * @apiSuccess {Number} value   Standardized value of denomination
     *
     * @apiSuccessExample Success-Response:
     *
     *     HTTP/1.1 200 OK
     *     [
     *          {
     *              "lang": "en-US",
     *              "name": "penny",
     *              "plural": "pennies",
     *              "value": 0.01
     *          },
     *          {
     *              "lang": "en-US",
     *              "name": "nickel",
     *              "plural": "nickels",
     *              "value": 0.05
     *          }
     *     ]
     *
     * @apiError NotFound
     *     Requested resource not found. 404
     *
     * @apiErrorExample 404 Response:
     *
     *     HTTP/1.1 404 Not Found
     *     {
     *         "error": "NotFound",
     *         "code": 3100
     *         "message": "Denominations not found for given language."
     *     }
     *
     * @apiError DatabaseError
     *     Error retrieving from database. 500
     *
     * @apiErrorExample 500 Response:
     *
     *     HTTP/1.1 500 Internal Server Error
     *     {
     *         "error": "DatabaseError",
     *         "code": 3100
     *         "message": "Error retrieving from database."
     *     }
     */
    public function getChangeDenomination(Request $request, Response $response, $args)
    {
        $lang = isset($args['lang']) ? $args['lang'] : null;

        if (is_null($lang)) {
            $lang = LANGUAGE;
        }

        try {
            $denoms = Change::getDenominationDatabase($lang);
        } catch (\Exception $e) {
            $errorResponse = $response->withJson(array(
                "error" => "DatabaseError",
                "code" => 3101,
                "message" => "Error retrieving from database."
            ));
            $errorResponse = $errorResponse->withStatus(500);
            return $errorResponse;
        }

        if (empty($denoms)) {
            // denominations not in database
            $errorResponse = $response->withJson(array(
                "error" => "NotFound",
                "code" => 3102,
                "message" => "Denominations not found for language $lang."
            ));
            $errorResponse = $errorResponse->withStatus(404);
            return $errorResponse;
        }

        $response = $response->withJson($denoms, null, JSON_UNESCAPED_UNICODE);
        return $response;
    }

    private static function getDenominationDatabase($lang = LANGUAGE)
    {
        $pdo = \App\Database::instance();
        $output = $pdo->run(
            "SELECT language.code AS lang, denomination.name, denomination.plural, denomination.value
            FROM cash_register.denomination
            LEFT JOIN cash_register.language ON denomination.lang_id = language.id
            WHERE language.code = '$lang';"
        )->fetchAll(\PDO::FETCH_ASSOC);

        return $output;
    }
}
