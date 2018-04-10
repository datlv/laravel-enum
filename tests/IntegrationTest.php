<?php namespace Datlv\Enum\Tests;

use Datlv\Enum\Tests\Stubs\IntergrationTestCase;

/**
 * Quản lý Enum bằng giao diện backend
 * Class IntegrationTest
 * @package Datlv\Enum\Tests
 * @author Minh Bang
 */
class IntegrationTest extends IntergrationTestCase
{
    /**
     * User bình thường truy cập trang quản lý enum
     */
    public function testUserAccessEnumManagementPage()
    {
        $response = $this->get('/backend/enum');
        // Yêu cầu đăng nhập khi truy cập
        $response->assertRedirect('/auth/login');
    }

    /**
     * Admin truy cập trang quản lý enum
     */
    public function testAdminAccessEnumManagementPage()
    {
        // Truy cập bằng quyền Admin
        $response = $this->actingAs($this->users['admin'])->get('/backend/enum');
        $response->assertStatus(403);
    }

    /**
     * Super Admin truy cập trang quản lý enum
     */
    public function testSuperAdminAccessEnumManagementPage()
    {
        $this->withoutExceptionHandling();
        // Truy cập bằng quyền Super Admin
        $response = $this->actingAs($this->users['super_admin'])->get('/backend/enum');
        $response->assertStatus(200);
    }
}