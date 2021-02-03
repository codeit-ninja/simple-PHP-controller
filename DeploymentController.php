<?php
namespace controllers;

use models\DeploymentModel;

class DeploymentController extends Controller
{
    /**
     * @var DeploymentModel
     */
    protected static $model = DeploymentModel::class;

    /**
     * @var DeploymentController
     */
    protected static $controller = DeploymentController::class;

    protected $validation = array(
        'project_id'    => '/^\d+$/',
        'name'          => '/^[a-zA-Z0-9\s_-]+$/',
        'created_by'    => '/^\d+$/',
        'path'          => '/^[a-zA-Z0-9\s_-]+$/',
        'branch'        => '/^([^-]+)?(-ex)?(-d)?(-b)?(-f)?(?!((-ex)|(-d)|(-b)|(-f)))(-.*)?$/',
        'hook'          => '/^[a-zA-Z0-9-]+$/',
        'server_id'     => '/^\d+|$/',
        'auto_deploy'   => '/^\d+$/'
    );

    protected $attributes = array(
        'project_id'    => null,
        'name'          => null,
        'created_by'    => null,
        'path'          => null,
        'branch'        => null,
        'hook'          => null,
        'server_id'     => null,
        'auto_deploy'   => null
    );

    protected $variables = array(
        'server'        => array( DeploymentController::class, 'getServer' )
    );

    /**
     * Get deployment instance
     *
     * @param   int     $deploymentId
     * 
     * @return  DeploymentController
     */
    public static function instance( int $deploymentId ) : DeploymentController
    {
        return new DeploymentController( static::$model::find( $deploymentId )->toArray() );
    }

    private function getServer() : ServerController
    {
        return ServerController::instance( (int) $this->get('server_id') );
    }
}
