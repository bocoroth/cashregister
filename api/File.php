<?php
/**
 * @author Matt Lisiak
 * File API
 *
 * Reads in raw file data and standardizes for further processing.
 */

namespace API;

// require our api configuration variables
require_once(realpath(dirname(__FILE__) . '/../config.php'));

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class File
{
    private $_app;

    public function __construct($app)
    {
        $this->_app = $app;
    }

    /**
     * @api {post} /api/file Parse a CSV file of amounts owed and paid.
     * @apiVersion 1.0.0
     * @apiName PostFile
     * @apiGroup File
     *
     * @apiParam {String} file_data     Contents of file to parse
     * @apiParam {String} [separator]   CSV separator to use for parsing (default: SEPARATOR constant defined by config.php [','])
     * @apiParam {String} [decimal]     Decimal point to use for parsing (default: DECIMAL constant defined by config.php ['.'])
     *
     * @apiSuccess {String} owed    Amount owed
     * @apiSuccess {String} paid    Amount customer paid
     *
     * @apiSuccessExample Success-Response:
     *
     *     HTTP/1.1 200 OK
     *     {
     *           [
     *               {
     *                   "owed": "2.12",
     *                   "paid": "3.00"
     *               },
     *               {
     *                   "owed": "1.97",
     *                   "paid": "2.00"
     *               }
     *           ]
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
     *         "code": 1000
     *         "message": "Unable to parse file data."
     *     }
     */
    public function postFile(Request $request, Response $response, $args)
    {
        $defaults = array(
            'file_data' => null,
            'separator' => SEPARATOR,
            'decimal' => DECIMAL
        );

        $post = $request->getParsedBody();

        $data = is_array($post) ? array_merge($defaults, $post) : null;

        if (is_null($data) || is_null($data['file_data'])) {
            // required file_data parameter is missing from request
            $errorResponse = $response->withJson(array(
                "error" => "InvalidParameter",
                "code" => 1001,
                "message" => "Unable to parse file data, file data is missing."
            ));
            $errorResponse = $errorResponse->withStatus(400);
            return $errorResponse;
        }

        $output = array();

        $decimal = $data['decimal'];
        $separator = $data['separator'];

        // first, divide by lines
        $rows = preg_split('/\R/', $data['file_data']);

        foreach ($rows as $row) {
            // split row values according to separator
            $parsed = preg_split("/$separator/", $row);

            if (count($parsed) !== 2) {
                // incorrect number of values in row
                $errorResponse = $response->withJson(array(
                    "error" => "InvalidParameter",
                    "code" => 1002,
                    "message" => "Unable to parse file data, incorrect number of values per row."
                ));
                $errorResponse = $errorResponse->withStatus(400);
                return $errorResponse;
            }

            // normalize values
            for ($i=0; $i<2; $i++) {
                // is_numeric (and other PHP functions) require English-style
                // decimals, so replace decimal with dot if necessary
                if ($decimal !== '.') {
                    $parsed[$i] = str_replace("$decimal", '.', $parsed[i]);
                }

                if (!is_numeric($parsed[$i])) {
                    // non-numeric values in file data
                    $errorResponse = $response->withJson(array(
                        "error" => "InvalidParameter",
                        "code" => 1003,
                        "message" => "Unable to parse file data, non-numeric value(s) detected."
                    ));
                    $errorResponse = $errorResponse->withStatus(400);
                    return $errorResponse;
                }

                $parsed[$i] = number_format($parsed[$i], 2, '.', '');
            }

            $output[] = array(
                "owed" => $parsed[0],
                "paid" => $parsed[1]
            );
        }

        $response = $response->withJson($output);
        return $response;
    }
}
