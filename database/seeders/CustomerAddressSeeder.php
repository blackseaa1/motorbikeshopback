<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;

class CustomerAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $customers = Customer::all();

        // Lấy một số tỉnh/huyện/xã mẫu.
        // Chú ý: Nếu bạn không seed dữ liệu địa lý, các dòng này sẽ thất bại.
        // Bạn có thể cần chạy `php artisan db:seed --class=VietnamZoneSeeder` (nếu có) trước.
        $hnProvince = Province::where('name', 'Thành phố Hà Nội')->first();
        $hcmProvince = Province::where('name', 'Thành phố Hồ Chí Minh')->first();

        $hnDistrict = null;
        $hnWard = null;
        if ($hnProvince) {
            $hnDistrict = District::where('province_id', $hnProvince->id)->where('name', 'Quận Cầu Giấy')->first();
            if ($hnDistrict) {
                $hnWard = Ward::where('district_id', $hnDistrict->id)->where('name', 'Phường Dịch Vọng Hậu')->first();
            }
        }

        $hcmDistrict = null;
        $hcmWard = null;
        if ($hcmProvince) {
            $hcmDistrict = District::where('province_id', $hcmProvince->id)->where('name', 'Quận 1')->first();
            if ($hcmDistrict) {
                $hcmWard = Ward::where('district_id', $hcmDistrict->id)->where('name', 'Phường Bến Nghé')->first();
            }
        }


        foreach ($customers as $customer) {
            if ($customer->email === 'customer_a@example.com' && $hnProvince && $hnDistrict && $hnWard) {
                CustomerAddress::firstOrCreate(
                    ['customer_id' => $customer->id, 'address_line' => 'Số 10, ngõ 100 Đường Trần Duy Hưng'],
                    [
                        'full_name' => $customer->name,
                        'phone' => $customer->phone ?? '09xxxxxxxx',
                        'province_id' => $hnProvince->id,
                        'district_id' => $hnDistrict->id,
                        'ward_id' => $hnWard->id,
                        'is_default' => true,
                    ]
                );
                CustomerAddress::firstOrCreate(
                    ['customer_id' => $customer->id, 'address_line' => 'Tòa nhà A, Khu đô thị mới Dịch Vọng'],
                    [
                        'full_name' => $customer->name,
                        'phone' => $customer->phone ?? '09xxxxxxxx',
                        'province_id' => $hnProvince->id,
                        'district_id' => $hnDistrict->id,
                        'ward_id' => $hnWard->id,
                        'is_default' => false,
                    ]
                );
            } elseif ($customer->email === 'customer_b@example.com' && $hcmProvince && $hcmDistrict && $hcmWard) {
                CustomerAddress::firstOrCreate(
                    ['customer_id' => $customer->id, 'address_line' => 'Chung cư X, Đường Nguyễn Huệ'],
                    [
                        'full_name' => $customer->name,
                        'phone' => $customer->phone ?? '09xxxxxxxx',
                        'province_id' => $hcmProvince->id,
                        'district_id' => $hcmDistrict->id,
                        'ward_id' => $hcmWard->id,
                        'is_default' => true,
                    ]
                );
            } else {
                // Fallback nếu không có dữ liệu địa lý hoặc khách hàng khác
                CustomerAddress::firstOrCreate(
                    ['customer_id' => $customer->id, 'address_line' => 'Địa chỉ mặc định'],
                    [
                        'full_name' => $customer->name,
                        'phone' => $customer->phone ?? '09xxxxxxxx',
                        // Gán ID mặc định nếu không tìm thấy dữ liệu địa lý
                        'province_id' => $hnProvince->id ?? 1,
                        'district_id' => $hnDistrict->id ?? 1,
                        'ward_id' => $hnWard->id ?? 1,
                        'is_default' => true,
                    ]
                );
            }
        }
    }
}