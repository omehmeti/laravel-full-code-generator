<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Storage;


class LaravelGeneratorController extends Controller
{   
   

    public function __construct(){
        
        
    }

    public function index(){
        $model_name = 'Members';
        $create_request_name = 'Members';
        $update_request_name = 'Members';
        $delete_request_name = 'Members';
        $controller_name = 'Members';
        $table_name = 'members';   
        $table_primary_key = 'user_id';

        $fields = array(
            0 => array(
                'name' => 'user_id',
                'type' => 'unsignedInteger', //same as primary
                'size' => '10',
                'check' => 'required|numeric',
               // 'is_primary' => 'false',
                'foreign_field' =>'id',
                'foreign_table' => 'users'
            ),
            1 => array(
                'name' => 'first_name',
                'type' => 'string',
                'size' => '30',
                'check' => 'required|alpha|max:30'
            ),
            2 => array(
                'name' => 'last_name',
                'type' => 'string',
                'size' => '30',
                'check' => 'required|alpha|max:30'
            ),
            3 => array(
                'name' => 'date_of_birthday',
                'type' => 'date',
                'check' => 'required|date|format'
            ),
            4 => array(
                'name' => 'start_date',
                'type' => 'date',
                'check' => 'required|date|format'
            ),
        );
        
        // Generate Model PHP 
        $this->generate_model($model_name,$table_name,$table_primary_key,$fields);

        // Generate Migraction Script PHP
        $this->generate_migration_script($model_name,$table_name,$fields);

        // Generate Create Request PHP
        $this->generate_create_request_file($create_request_name,$fields);

        // Generate Update Request PHP
        $this->generate_update_request_file($update_request_name,$fields);

        // Generate Delete Request PHP
        $this->generate_delete_request_file($delete_request_name,$fields);

        // Generate Controller PHP
        $this->generate_controller_file($controller_name,$fields,$model_name,$table_name,$table_primary_key,$create_request_name,$update_request_name,$delete_request_name);

        return 'OK';
    }
    
    public function generate_model($model_name,$table_name,$table_primary_key,$fields){ 

    
        $number_of_fields = sizeof($fields);
        $fields_string='';
        
        for ($i = 0; $i < $number_of_fields ; $i++) {
            $fields_string .= "'". $fields[$i]["name"] . "'";
            if($i != $number_of_fields - 1){
                $fields_string .=",";
            }

        }
    
        $model = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ".$model_name."Model extends Model
{
    protected \$table = '".$table_name."';

    public \$incrementing = false;

    protected \$primaryKey = '".$table_primary_key."';

    protected \$fillable = [".$fields_string."];

    protected \$hidden = ['created_at','updated_at'];
    
    public function user(){
        return \$this->belongsTo('App\User');
    }
}";
        
        Storage::disk('local')->put("/Model/".$model_name."Model.php", $model);

        return "Model Generated Successfully";
    }


    public function generate_migration_script($model_name,$table_name,$fields){

        $migration_script="<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ".$model_name."Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('".$table_name."', function (Blueprint \$table) {";

        $number_of_fields = sizeof($fields);
        $fields_string= "";
        $fields_string.="\n\n\t\t";
        $foreign_keys = "";
        
        for ($i = 0; $i < $number_of_fields ; $i++) {
                
                $fields_string .= "\$table->". $fields[$i]["type"] . "('".$fields[$i]["name"]."'";
                isset($fields[$i]["size"]) ? $fields_string .= "," . $fields[$i]["size"] .")" : $fields_string .= ")";
                isset($fields[$i]["default_value"]) ? $fields_string .= "->default('". $fields[$i]["default_value"] ."')" : '';
                isset($fields[$i]["is_primary"]) ? $fields_string .= "->primary()" : '';
                $fields_string .=";\n\t\t";

                //Check for foreign keys
                isset($fields[$i]["foreign_field"]) ? $foreign_keys .= "\$table->foreign('".$fields[$i]["name"]."')->references('".$fields[$i]["foreign_field"]."')->on('".$fields[$i]["foreign_table"]."');\n\t\t" : "";
        }    

        
        $migration_script .= $fields_string;
        $migration_script .= "\n\t\t\$table->timestamps();";

        if (isset($foreign_keys)){
            $migration_script .= "\n\n\t\t//Forign key contraints";
            $migration_script .= "\n\n\t\t".$foreign_keys;    
        }
        
        $migration_script .= "\n\t\t});";
        
        $migration_script .= "\n\n\t}";

        $migration_script .= "
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    
    public function down(){
        Schema::drop('".$table_name."');
    }
        ";
      


        $migration_script .= "\n\n}";
        Storage::disk('local')->put("/Migration_scripts/2017_01_01_".$table_name."_table.php", $migration_script);

        return "Model Generated Successfully";
    }

    
    public function generate_create_request_file($create_request_name,$fields){
        $number_of_fields = sizeof($fields);
        $fields_string= "";
        $fields_string.="\n\n\t\t\t";

        //'name' =>'required|alpha|max:50',
        for ($i = 0; $i < $number_of_fields ; $i++) {
            isset($fields[$i]["check"]) ? $fields_string .= "'".$fields[$i]["name"]."' =>'".  $fields[$i]["check"] ."'" : "";
            if ($i<$number_of_fields - 1) {
                $fields_string .= ","; 
            }
            $fields_string .= "\n\t\t\t"; 
        }    
        $create_request_file = "
<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class Create".$create_request_name."equest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [";
            
    $create_request_file.= $fields_string;

    $create_request_file.=
"
        ];
    }


    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  \$errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array \$errors)
    {
        return response()->json(['message'=> \$errors,'code'=>422],422);
    }
}";
        

        Storage::disk('local')->put("/Request/Create".$create_request_name."Request.php", $create_request_file);

        return "Model Generated Successfully";
    }

    public function generate_update_request_file($update_request_name,$fields){
        $number_of_fields = sizeof($fields);
        $fields_string= "";
        $fields_string.="\n\n\t\t\t";

        //'name' =>'required|alpha|max:50',
        for ($i = 0; $i < $number_of_fields ; $i++) {
            isset($fields[$i]["check"]) ? $fields_string .= "'".$fields[$i]["name"]."' =>'".  $fields[$i]["check"] ."'" : "";
            if ($i<$number_of_fields - 1) {
                $fields_string .= ","; 
            }
            $fields_string .= "\n\t\t\t"; 
        }    
        $update_request_file = "
<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class Update".$update_request_name."Request extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [";
            
    $update_request_file.= $fields_string;

    $update_request_file.=
"
             //TODO: Username must be nullable in migration scripts
        ];
    }


    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  \$errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array \$errors)
    {
        return response()->json(['message'=> \$errors,'code'=>422],422);
    }
}";
        

        Storage::disk('local')->put("/Request/Update".$update_request_name."Request.php", $update_request_file);

        return "Model Generated Successfully";
    }

    public function generate_delete_request_file($delete_request_name,$fields){
        $number_of_fields = sizeof($fields);
        $fields_string= "";
        $fields_string.="\n\n\t\t\t";

        for ($i = 0; $i < $number_of_fields ; $i++) {
            if(isset($fields[$i]["is_primary"]) && $fields[$i]["is_primary"]) {
                $fields_string .= "'".$fields[$i]["name"]."' =>'".  $fields[$i]["check"] ."'\n";
                break;
            }   
             
        }    
        $delete_request_file = "<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class Delete".$delete_request_name."Request extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [";
            
    
    $delete_request_file.= $fields_string;

    $delete_request_file.=
"
        ];
    }


    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  \$errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array \$errors)
    {
        return response()->json(['message'=> \$errors,'code'=>422],422);
    }
}";
        

        Storage::disk('local')->put("/Request/Delete".$delete_request_name."Request.php", $delete_request_file);

        return "Model Generated Successfully";
    }

    public function generate_controller_file($controller_name,$fields,$model_name,$table_name,$table_primary_key,$create_request_name,$update_request_name,$delete_request_name){

        $number_of_fields = sizeof($fields);
        $fields_string= "";
        $fields_string.="";
        $update_fields_string ="";
        $update_fields_string2 ="";
        
        for ($i = 0; $i < $number_of_fields ; $i++) {
            
            $update_fields_string.="\n\t\t\$".$fields[$i]["name"]." = \$request->get('".$fields[$i]["name"]."');";

            if($fields[$i]["type"] == 'date') {

                $fields_string.="\t\t\$values['".$fields[$i]["name"]."'] = Carbon::createFromFormat('d/m/Y', \$request->".$fields[$i]["name"].");\n";
                
                $update_fields_string2.= "\n\t\t\$".lcfirst($model_name)."->start_date = Carbon::createFromFormat('d.m.Y', \$".$fields[$i]["name"].");";  
            }else{
                
                $update_fields_string2.= "\n\t\t\$".lcfirst($model_name)."->".$fields[$i]["name"]." = \$".$fields[$i]["name"].";";
                
            }   
             
        }    


        $controller_file = "<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\\".$model_name."Model;
use App\Http\Requests\Create".$create_request_name."Request;
use App\Http\Requests\Update".$update_request_name."Request;
use App\Http\Requests\Delete".$delete_request_name."Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers;
use Carbon\Carbon;

class ".$controller_name."Controller extends Controller
{
    public function __construct(){
       \$this->middleware('auth:api');
    }

    public function index(){
        \$".lcfirst($model_name)." = Cache::remember('".$table_name."',60,function(){
            return User::simplePaginate(15);
        });

        return response()->json( ['data' => \$".lcfirst($model_name)."],200);
    }

    public function store( Create".$create_request_name."Request \$request ){
        \n\t\t\$values = \$request->all();\n\n".$fields_string."
        ".$model_name."Model::create(\$values);

        return response()->json(['message'=>'New ".$model_name." is added successfully'],200);
    }

    public function update(Update".$update_request_name."Request \$request){
        \n\t\t\$".lcfirst($model_name)." = ".$model_name."Model::find(\$request->".$table_primary_key.");
        
        if(!\$".lcfirst($model_name)."){
            return response()->json(['message'=>'There is not any data associated with this key provided','code'=>404],404);
        }     

        ". $update_fields_string."

       ". $update_fields_string2."

        \$".lcfirst($model_name)."->save();
        return response()->json(['message'=>'".$model_name." has been updated'],200);
    }

    public function show(\$".$table_primary_key."){

        \$".lcfirst($model_name)." = ".$model_name."Model::find(\$".$table_primary_key.");
        if(!\$".lcfirst($model_name)."){
            return response()->json(['message'=>'There is not any data associated with this key provided','code'=>404],404);
        }else{
            return response()->json(['data'=>\$".lcfirst($model_name)."],200);
        }
    }

    public function destroy(Delete".$delete_request_name."Request \$request){
        
        \$".lcfirst($model_name)." = ".$model_name."Model::find(\$request->".$table_primary_key.");
        
        if(!\$".lcfirst($model_name)."){
            return response()->json(['message'=>'There is not any data associated with this key provided','code'=>404],404);
        }       

        \$".lcfirst($model_name)."->delete();
        return response()->json(['message'=>'".$model_name." is deleted successfully'],200);
        
    }

}
";

    Storage::disk('local')->put("/Controller/".$controller_name."Controller.php", $controller_file);

        return "Model Generated Successfully";
    }




}