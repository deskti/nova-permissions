<?php
namespace Eminiarts\NovaPermissions\Nova;

use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\MorphToMany;
use Eminiarts\NovaPermissions\Checkboxes;
use Eminiarts\NovaPermissions\Role as RoleModel;

class Role extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = RoleModel::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Id', 'id')
                ->rules('required')
                ->hideFromIndex()
            ,
            Text::make(__('Name'), 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:' . config('permission.table_names.roles'))
                ->updateRules('unique:' . config('permission.table_names.roles') . ',name,{{resourceId}}')

            ,
            Select::make(__('Guard Name'), 'guard_name')
                ->options([
                    'web' => 'Auth session',
                    'sanctum' => 'Auth api tokens'
                ])
                ->displayUsingLabels()
                ->rules(['required')
                ->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                })
            ,
            Checkboxes::make(__('Permissions'), 'prepared_permissions')
                ->withGroups()
                ->options($this->loadPermissions()->map(function ($permission, $key) {
                    return [
                        'group'  => __(ucfirst($permission->group)),
                        'option' => $permission->name,
                        'label'  => '&nbsp;'.__($permission->name),
                    ];
                })
                    ->groupBy('group')
                    ->toArray()),

            Text::make(__('Users'), function () {
                return $this->users()->count();
            })->exceptOnForms(),

            MorphToMany::make('Users', 'users', 'App\Nova\User')->searchable(),
        ];
    }

    /**
     * Load all permissions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadPermissions()
    {
        $permissionClass = config('permission.models.permission');

        return $permissionClass::all();
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    public static function label()
    {
        return __('Roles');
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    public static function singularLabel()
    {
        return __('Role');
    }
}
