<?php namespace App\Libraries;

use App\Http\Controllers\Controller;
use App\Models\User;
use Response;

abstract class TestController extends Controller
{
    // method descriptions to be shown on the index page
    protected $methodDescriptions = [
        'methodName' => 'Explanation of what this method does.',
    ];

    /* ---------------------------------------------------------------------------------------------------------------------------------- TESTS -+- */

    public function dataTableTest1()
    {
        $this->dt([
            'id'      => 1,
            'name'    => 'Jane',
            'surname' => 'Doe',
        ]);

        $this->dt(
        [
                'id'      => 2,
                'name'    => 'Joe',
                'surname' => 'Doe',
            ]);

        $opts = [
            // providing custom headers
            'headers' => [
                '#',
                'Name',
                'Surname'
            ],
            'title'   => 'Custom headers test'
        ];

        $this->dt(false, $opts);

        $this->dt([
            [
                'id'      => 3,
                'name'    => 'Jenny',
                'surname' => 'Doe',
            ],
            [
                'id'      => 4,
                'name'    => 'Johanna',
                'surname' => 'Doe',
            ],
            [
                'id'      => 5,
                'name'    => 'Jade',
                'surname' => 'Doe',
            ],
        ]);
    }

    public function dataTableTest2()
    {
        $this->dt(false, [
            'title' => 'Laravel collection dump test'
        ]);
        $this->dt(User::take(10)->get());
    }

    public function dataTableTest3()
    {
        $this->dt(false, [
            'title' => 'Data standardization test'
        ]);

        // test for column standardization.
        $this->dt([
            [
                'id'         => 1,
                'name'       => 'Jane',
                'surname'    => 'Doe',
                'age'        => '45',
                'country'    => 'Russia',
                'city'       => 'Tunguska',
                'occupation' => 'Placeholder',
                'company'    => 'web',
                'sex'        => 'female',
            ],
            [
                'id'      => 2,
                'name'    => 'Joe',
                'surname' => 'Doe',
                'age'     => '45',
                'country' => 'Russia',
                'city'    => 'Tunguska',
                'company' => 'web',
                'sex'     => 'male',
            ],
            [
                'name'    => 'Jenny',
                'surname' => 'Doe',
                'age'     => '12',
                'city'    => 'Tunguska',
            ],
            [
                'id'         => 4,
                'name'       => 'Johanna',
                'surname'    => 'Doe',
                'age'        => '15',
                'country'    => 'Russia',
                'city'       => 'Tunguska',
                'occupation' => 'Placeholder',
                'company'    => 'web',
            ],
            [
                'id'      => 5,
                'name'    => 'Jade',
                'surname' => 'Doe',
                'age'     => '22',
                'country' => 'Russia',
                'company' => 'web',
                'sex'     => 'female',
            ],
        ]);
    }


    /* ------------------------------------------------------------------------------------------------------------------------------ UTILITIES -+- */

    /* ----------------------------------------------------------------------------------------------------------------------------- data Table -+- */
    /**
     * @param array      $data
     * @param array|bool $_opts
     */
    final public function dt($data, $_opts = false)
    {
        static $initialized = null;
        static $rows = [];
        static $opts = [
            // optional title for the page
            'title'     => false,
            // will use these headers if provided. Will fall back to array/object keys if not.
            'headers'   => [],
            // use monospace font for the data.
            'monospace' => false,
        ];

        if ($_opts) {
            $opts = array_merge([
                // optional title for the page
                'title'     => false,
                // will use these headers if provided. Will fall back to array/object keys if not.
                'headers'   => [],
                // use monospace font for the data.
                'monospace' => false,
            ], $_opts);
        }

        if (is_object($data) && get_class($data) == 'Illuminate\Database\Eloquent\Collection')
            $data = $data->toArray();

        if (is_array($data) && count($data)) {
            $firstRow = array_first($data, function(){
                return true;
            });
            // check if the data is a row or multiple rows
            if (is_array($firstRow) || is_object($firstRow) && !$this->isStringable($firstRow)) {
                // data has multiple rows.
                $rows = array_merge($rows, $data);
            } else {
                // data is a single row.
                $rows[] = $data;
            }
        }

        /* ------------------------------------------------------------------------------------------------------------------ shutdown function -+- */
        // > dump the data to table and echo the view.
        // take variables by reference, so those will stay up-to-date
        $shutdown = function() use (&$rows, &$opts) {

            /* ------------------------------------------------------------------------------------------------------------------------------ 1 -+- */
            // > convert object rows to arrays.
            foreach ($rows as $k => $row) {
                if (is_object($row))
                    $rows[$k] = get_object_vars($row);
            }

            /* ------------------------------------------------------------------------------------------------------------------------------ 2 -+- */
            // > standardize array keys.
            // craft a ruler array keep record of all the different keys across the data.
            $ruler = [];
            foreach ($rows as $row) {
                // collect keys in ruler array's keys.
                $ruler = array_merge($ruler, array_fill_keys(array_keys($row), ''));
            }

            // apply ruler set of keys to all arrays to standardize array keys.
            foreach ($rows as $k => $row) {
                $rows[$k] = array_merge($ruler, $row);
            }

            /* ------------------------------------------------------------------------------------------------------------------------------ 3 -+- */
            // > if column headers are not specified, get headers from array keys/object attribute names.
            if (!$opts['headers']) {
                $opts['headers'] = array_keys($ruler);
            }

            /* ------------------------------------------------------------------------------------------------------------------------------ 4 -+- */
            // > convert non stringable columns to json.
            foreach ($rows as &$row) {
                foreach ($row as &$column) {
                    $column = $this->isStringable($column) ? $column : json_encode($column);
                }
            }

            $view = view('test.data-table')
                ->with('data', $rows)
                ->with('opts', $opts)
                ->__toString();

            echo $view;
        };

        // register shutdown function
        if ($initialized == null) {
            register_shutdown_function($shutdown);
            $initialized = true;
        }
    }

    /**
     * @param $input
     *
     * @return bool
     */
    private function isStringable($input)
    {
        return (
            (!is_array($input)) &&
            (
                (!is_object($input) &&
                    settype($input, 'string') !== false) ||
                (is_object($input) && method_exists($input, '__toString'))
            )
        );
    }
    /* --------------------------------------------------------------------------------------------------------------------------- MAIN METHODS -+- */

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    final public function route($method = 'index', $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $args = array_slice(func_get_args(), 1);
        foreach ($args as $k => $arg) {
            if ($arg == null) unset($args[$k]);
        }

        // convert method names to camelCase
        $method = camel_case($method);

        echo "
        <a href='/test' style=\"position: fixed; bottom: 15px; width: 300px; margin-left: -150px; left: 50%; z-index: 100000; background: rgba(0,0,0,0.4); color: white; text-align: center; padding: 10px 0; font-family: monospace; font-size: 16px;\">
            return to test index
        </a>
        ";

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $args);
        } else {
            dd('Method does not exists.');
        }
    }

    final public function index()
    {
        $reflection = new \ReflectionClass(get_class($this));
        $methods = [];

        $reflectionMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($reflectionMethods as $method) {

            if ($method->class == $reflection->getName() && !in_array($method->name, ['__construct', 'route', 'index'])) {
                $parameters = $method->getParameters();
                $name = snake_case($method->name, '-');
                $url = "/test/" . $name;
                $urlSignature = $url;

                foreach ($parameters as $parameter) {
                    $urlSignature .= "/" . $parameter->name;
                    if ($parameter->isDefaultValueAvailable() && $parameter->getDefaultValue()) {
                        $url .= "/" . $parameter->getDefaultValue();
                        $urlSignature .= "(" . $parameter->getDefaultValue() . ")";
                    } else {
                        $url .= "/" . $parameter->name;
                    }
                }
                $methods[] = [
                    'name'          => $name,
                    'url'           => $url,
                    'url-signature' => $urlSignature,
                    'parameters'    => $parameters,
                    'description'   => array_get($this->methodDescriptions, $name, array_get($this->methodDescriptions, $method->name, ''))
                ];
            }
        }

        return view('test.index')
            ->with('methods', $methods);
    }

}
