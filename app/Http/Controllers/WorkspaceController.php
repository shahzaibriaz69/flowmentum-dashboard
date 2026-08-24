namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Message; 
use App\Models\Deal;
use App\Models\Campaign;

class WorkspaceController extends Controller
{
    public function show($page = 'people')
    {
        $data = ['page' => $page];

        switch ($page) {
            case 'people':
                $data['people'] = Person::latest()->get();
                break;
            case 'inbox':
                $data['messages'] = Message::latest()->get(); 
                break;
            case 'pipeline':
                $data['deals'] = Deal::all();
                break;
            case 'marketing':
                $data['campaigns'] = Campaign::all();
                break;
            case 'automations':
                $data['workflows'] = []; // Dynamic model/array
                break;
            case 'sites':
                $data['sites'] = []; // Dynamic model/array
                break;
            default:
                // 404 page render hoga
                break;
        }

        return view('workspace', $data);
    }
}