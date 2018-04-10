<?php

namespace Datlv\Enum;

use Datlv\Kit\Extensions\BackendController;
use Datlv\Kit\Traits\Controller\PositionActions;
use Datlv\Enum\Request as EnumRequest;
use Enum;

/**
 * Class Controller
 *
 * @package Datlv\Enum
 */
class Controller extends BackendController {
    use PositionActions;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array Danh sách các loại enum
     */
    protected $types;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Cache cho readOnlyEnumTypes()
     *
     * @var array
     */
    protected $readOnlyEnumTypes;

    /**
     * Danh sách enum types chỉ đọc, định dạng ['type' => 'msg cảnh báo']
     *
     * @return array
     */
    protected function readOnlyEnumTypes() {
        return [];
    }

    /**
     * @return array
     */
    protected function getReadOnlyEnumTypes() {
        if ( is_null( $this->readOnlyEnumTypes ) ) {
            $this->readOnlyEnumTypes = $this->readOnlyEnumTypes();
        }

        return $this->readOnlyEnumTypes;
    }

    /**
     * @param string $type
     */
    protected function switchType( $type = null ) {
        $this->type = $type ?: Enum::firstType( 'id' );
        abort_unless( Enum::isRegistered( $this->type ), 404, trans( 'enum::common.not_found' ) );
        session( [ 'backend.enum.type' => $this->type ] );
    }

    /**
     * @return string
     */
    protected function getType() {
        return session( 'backend.enum.type', Enum::firstType( 'id' ) );
    }

    /**
     * @return string
     */
    protected function getTypeName() {
        return mb_fn_str( Enum::get( $this->getType() )['title'] );
    }

    /**
     * @param string|null $type
     *
     * @return \Illuminate\View\View
     */
    public function index( $type = null ) {
        if ( Enum::isEmpty() ) {
            $this->buildHeading( trans( 'enum::common.manage' ), 'fa-list', [ '#' => trans( 'enum::common.enums' ) ] );

            return view( 'enum::empty' );
        }
        $this->switchType( $type );
        $types = Enum::groupedTypes();
        $current = $this->getType();
        $typeName = $this->getTypeName();
        $enums = EnumModel::whereType( $current )->orderPosition()->get();
        $this->buildHeading(
            [ trans( 'enum::common.manage' ), $typeName ],
            'fa-list',
            [ '#' => $typeName ]
        );
        $readOnly = isset( $this->getReadOnlyEnumTypes() [$this->getType()] ) ? $this->getReadOnlyEnumTypes() [$this->getType()] : false;

        return view( 'enum::index', compact( 'types', 'current', 'typeName', 'enums', 'readOnly' ) );
    }

    /**
     * @return \Illuminate\View\View
     * @throws \Laracasts\Presenter\Exceptions\PresenterException
     */
    public function create() {
        $enum = new EnumModel();
        $url = route( $this->route_prefix . 'backend.enum.store' );
        $method = 'post';

        return view( 'enum::form', compact( 'enum', 'url', 'method' ) );
    }

    /**
     * @param \Datlv\Enum\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function store( EnumRequest $request ) {
        $error = isset( $this->getReadOnlyEnumTypes() [$this->getType()] ) ? 'error' : 'success';
        if ( $error == 'success' ) {
            $enum = new EnumModel();
            $enum->fill( $request->all() );
            $enum->fillNextPosition();
            $enum->type = $this->getType();
            $enum->save();
        }


        return view(
            'kit::_modal_script',
            [
                'message'    => [
                    'type'    => $error,
                    'content' => trans( "common.create_object_{$error}", [ 'name' => $this->getTypeName() ] ),
                ],
                'reloadPage' => $error == 'success',
            ]
        );
    }

    /**
     * @param \Datlv\Enum\EnumModel $enum
     *
     * @return \Illuminate\View\View
     */
    public function edit( EnumModel $enum ) {
        $url = route( $this->route_prefix . 'backend.enum.update', [ 'enum' => $enum->id ] );
        $method = 'put';

        return view( 'enum::form', compact( 'enum', 'url', 'method' ) );
    }

    /**
     * @param \Datlv\Enum\Request $request
     * @param \Datlv\Enum\EnumModel $enum
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update( EnumRequest $request, EnumModel $enum ) {
        $error = isset( $this->getReadOnlyEnumTypes()[$enum->type] ) ? 'error' : 'success';
        if ( $error == 'success' ) {
            $enum->fill( $request->all() );
            $enum->save();
        }

        return view(
            'kit::_modal_script',
            [
                'message'    => [
                    'type'    => $error,
                    'content' => trans( "common.update_object_{$error}", [ 'name' => $this->getTypeName() ] ),
                ],
                'reloadPage' => $error == 'success',
            ]
        );
    }

    /**
     * @param \Datlv\Enum\EnumModel $enum
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy( EnumModel $enum ) {
        if ( $enum->isUsed() || isset( $this->getReadOnlyEnumTypes() [$enum->type] ) ) {
            return response()->json(
                [
                    'type'    => 'error',
                    'content' => trans( 'common.delete_object_error', [ 'name' => $this->getTypeName() ] ),
                ]
            );
        } else {
            $enum->delete();

            return response()->json(
                [
                    'type'    => 'success',
                    'content' => trans( 'common.delete_object_success', [ 'name' => $this->getTypeName() ] ),
                ]
            );
        }
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     */
    protected function positionModel( $id ) {
        return EnumModel::find( $id );
    }

    /**
     * @return string
     */
    protected function positionName() {
        return $this->getTypeName();
    }
}
