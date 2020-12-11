<?php
/**
 * Retour plugin for Craft CMS 3.x
 *
 * Retour allows you to intelligently redirect legacy URLs, so that you don't
 * lose SEO value when rebuilding & restructuring a website
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\retour\controllers;

use nystudio107\retour\helpers\Permission as PermissionHelper;

use Craft;
use craft\db\Query;
use craft\helpers\UrlHelper;
use craft\web\Controller;

use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * @author    nystudio107
 * @package   Retour
 * @since     3.0.0
 */
class TablesController extends Controller
{
    // Constants
    // =========================================================================

    const HANDLED_MAP = [
        'handled' => 1,
        'nothandled' => 0,
    ];

    const SORT_MAP = [
        'DESC' => SORT_DESC,
        'ASC' => SORT_ASC,
    ];

    const ALLOWED_STATS_SORT_FIELDS = [
        'redirectSrcUrl',
        'referrerUrl',
        'remoteIp',
        'hitCount',
        'hitLastTime',
        'handledByRetour',
    ];

    const ALLOWED_REDIRECTS_SORT_FIELDS = [
        'redirectSrcUrl',
        'redirectDestUrl',
        'redirectMatchType',
        'siteId',
        'redirectHttpCode',
        'hitCount',
        'hitLastTime',
    ];

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array
     */
    protected $allowAnonymous = [
    ];

    // Public Methods
    // =========================================================================

    /**
     * Handle requests for the dashboard statistics table
     *
     * @param string      $sort
     * @param int         $page
     * @param int         $per_page
     * @param string      $filter
     * @param int         $siteId
     * @param string|null $handled
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function actionDashboard(
        string $sort = 'hitCount|desc',
        int $page = 1,
        int $per_page = 20,
        $filter = '',
        $siteId = 0,
        $handled = 'all'
    ): Response {
        PermissionHelper::controllerPermissionCheck('retour:dashboard');
        $data = [];
        $sortField = 'hitCount';
        $sortType = 'DESC';
        // Figure out the sorting type
        if ($sort !== '') {
            if (strpos($sort, '|') === false) {
                $sortField = $sort;
            } else {
                list($sortField, $sortType) = explode('|', $sort);
            }
        }
        $sortType = strtoupper($sortType);
        $sortType = self::SORT_MAP[$sortType] ?? self::SORT_MAP['DESC'];
        // Validate untrusted data
        if (!in_array($sortField, self::ALLOWED_STATS_SORT_FIELDS, true)) {
            throw new BadRequestHttpException(Craft::t('retour', 'Invalid sort field specified.'));
        }
        // Query the db table
        $offset = ($page - 1) * $per_page;
        $query = (new Query())
            ->from(['{{%retour_stats}}'])
            ->offset($offset)
            ->limit($per_page)
            ->orderBy([$sortField => $sortType])
            ->filterWhere(['like', 'redirectSrcUrl', $filter])
            ->orFilterWhere(['like', 'referrerUrl', $filter])
            ;
        if ((int)$siteId !== 0) {
            $query->andWhere(['siteId' => $siteId]);
        }
        if ($handled !== 'all') {
            $query->andWhere(['handledByRetour' => self::HANDLED_MAP[$handled]]);
        }
        $stats = $query->all();
        if ($stats) {
            // Add in the `addLink` field
            foreach ($stats as &$stat) {
                $stat['addLink'] = '';
                if (!$stat['handledByRetour']) {
                    $encodedUrl = urlencode('/'.ltrim($stat['redirectSrcUrl'], '/'));
                    $stat['addLink'] = UrlHelper::cpUrl('retour/add-redirect', [
                        'defaultUrl' => $encodedUrl
                    ]);
                }
            }
            // Format the data for the API
            $data['data'] = $stats;
            $count = $query->count();
            $data['links']['pagination'] = [
                'total' => $count,
                'per_page' => $per_page,
                'current_page' => $page,
                'last_page' => ceil($count / $per_page),
                'next_page_url' => null,
                'prev_page_url' => null,
                'from' => $offset + 1,
                'to' => $offset + ($count > $per_page ? $per_page : $count),
            ];
        }

        return $this->asJson($data);
    }

    /**
     * Handle requests for the dashboard redirects table
     *
     * @param string $sort
     * @param int    $page
     * @param int    $per_page
     * @param string $filter
     * @param null   $siteId
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function actionRedirects(
        string $sort = 'hitCount|desc',
        int $page = 1,
        int $per_page = 20,
        $filter = '',
        $siteId = 0
    ): Response {
        PermissionHelper::controllerPermissionCheck('retour:redirects');
        $data = [];
        $sortField = 'hitCount';
        $sortType = 'DESC';
        // Figure out the sorting type
        if ($sort !== '') {
            if (strpos($sort, '|') === false) {
                $sortField = $sort;
            } else {
                list($sortField, $sortType) = explode('|', $sort);
            }
        }
        $sortType = strtoupper($sortType);
        $sortType = self::SORT_MAP[$sortType] ?? self::SORT_MAP['DESC'];
        // Validate untrusted data
        if (!in_array($sortField, self::ALLOWED_REDIRECTS_SORT_FIELDS, true)) {
            throw new BadRequestHttpException(Craft::t('retour', 'Invalid sort field specified.'));
        }
        // Query the db table
        $offset = ($page - 1) * $per_page;
        $query = (new Query())
            ->from(['{{%retour_static_redirects}}'])
            ->offset($offset)
            ->limit($per_page)
            ->orderBy([$sortField => $sortType])
            ->filterWhere(['like', 'redirectSrcUrl', $filter])
            ->orFilterWhere(['like', 'redirectDestUrl', $filter])
           ;
        if ((int)$siteId !== 0) {
            $query->andWhere(['siteId' => $siteId]);
        }
        $redirects = $query->all();
        // Add in the `deleteLink` field and clean up the redirects
        foreach ($redirects as &$redirect) {
            // Make sure the destination URL is not a regex
            if ($redirect['redirectMatchType'] !== 'exactmatch') {
                if (preg_match("/\$\d+/", $redirect['redirectDestUrl'])) {
                    $redirect['redirectDestUrl'] = '';
                }
            }
            // Handle extracting the site name
            $redirect['siteName'] = Craft::t('retour', 'All Sites');
            if ($redirect['siteId']) {
                $sites = Craft::$app->getSites();
                $site = $sites->getSiteById($redirect['siteId']);
                if ($site) {
                    $redirect['siteName'] = $site->name;
                }
            }

            $redirect['editLink'] = UrlHelper::cpUrl('retour/edit-redirect/'.$redirect['id']);
        }
        // Format the data for the API
        if ($redirects) {
            $data['data'] = $redirects;
            $count = $query->count();
            $data['links']['pagination'] = [
                'total' => $count,
                'per_page' => $per_page,
                'current_page' => $page,
                'last_page' => ceil($count / $per_page),
                'next_page_url' => null,
                'prev_page_url' => null,
                'from' => $offset + 1,
                'to' => $offset + ($count > $per_page ? $per_page : $count),
            ];
        }

        return $this->asJson($data);
    }

    // Protected Methods
    // =========================================================================
}
