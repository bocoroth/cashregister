<?php
/**
 * @author Matt Lisiak
 * Translation API
 *
 * Returns translations for blocks of site text.
 */

namespace API;

// require our api configuration variables
require_once(realpath(dirname(__FILE__) . '/../config.php'));

require_once(realpath(dirname(__FILE__) . '/../Database.php'));

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Translation
{
    private $_app;

    public function __construct($app)
    {
        $this->_app = $app;
    }

    /**
     * @api {get} /api/translation/{lang}/{code}  Returns a translation of text for a given language and block code
     * @apiVersion 1.0.0
     * @apiName GetTranslation
     * @apiGroup Translation
     *
     * @apiParam {String} lang      Language to get translation for
     * @apiParam {String} code      Shortcode for translated text block to return
     *
     * @apiSuccess {String} lang    Language code
     * @apiSuccess {String} code    Text block shortcode
     * @apiSuccess {String} text    Translated text block
     *
     * @apiSuccessExample Success-Response:
     *
     *     HTTP/1.1 200 OK
     *     {
     *           "lang": "fr-FR",
     *           "code": "AMOUNT_OWED",
     *           "text": "Montant dû:"
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
     *         "code": 2000
     *         "message": "Translation not found"
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
    public function getTranslation(Request $request, Response $response, $args)
    {
        $lang = isset($args['lang']) ? $args['lang'] : null;
        $code = isset($args['code']) ? $args['code'] : null;

        try {
            $pdo = \App\Database::instance();
            $output = $pdo->run(
                "SELECT language.code AS lang, localization.code, localization.content AS text
                FROM cash_register.localization
                LEFT JOIN cash_register.language ON localization.lang_id = language.id
                WHERE language.code = '$lang' AND localization.code = '$code';"
            )->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $errorResponse = $response->withJson(array(
                "error" => "DatabaseError",
                "code" => 2001,
                "message" => "Error retrieving from database."
            ));
            $errorResponse = $errorResponse->withStatus(500);
            return $errorResponse;
        }

        if (empty($output)) {
            // translation not in database
            $errorResponse = $response->withJson(array(
                "error" => "NotFound",
                "code" => 2002,
                "message" => "Translation not found."
            ));
            $errorResponse = $errorResponse->withStatus(404);
            return $errorResponse;
        }

        $response = $response->withJson($output, null, JSON_UNESCAPED_UNICODE);
        return $response;
    }
}
