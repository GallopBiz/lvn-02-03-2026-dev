namespace App\Imports;

use App\Models\Areamaster;
use Maatwebsite\Excel\Concerns\ToModel;

class AreaImport implements ToModel
{
    public function model(array $row)
    {
        return new Areamaster([
            'area_name' => $row[0], // Ensure the correct column index
        ]);
    }
}
