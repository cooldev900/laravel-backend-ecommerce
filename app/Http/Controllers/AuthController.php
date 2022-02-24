<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\Company;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserPermission;
use App\Providers\LoginHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('jwt_auth', ['except' => ['login', 'refresh', 'logout', 'sendPasswordResetLink', 'callResetPassword']]);
    }

    /**
     * Send password reset link.
     */

    public function sendPasswordResetLink(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return back()->with('failed', 'Failed! email is not registered.');
            }

            DB::table('password_resets')->where(['email' => $request->email])->delete();

            $token = Str::random(60);
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

            Mail::to($request->email)->send(new ResetPassword($user->name, $token));

            if (Mail::failures() != 0) {
                return response()->json([
                    'message' => 'Success! password reset link has been sent to your email',
                    'status' => 'success',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Fail_sent_reset_email',
                    'message' => 'Failed! there is some issue with email provider',
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'Fail_sent_reset_email',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle reset password
     */
    public function callResetPassword(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
            ]);

            $updatePassword = DB::table('password_resets')
                ->where([
                    'email' => $request->email,
                    'token' => $request->token,
                ])
                ->first();
            if (!$updatePassword) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Fail_reset_password',
                    'message' => 'Password reset token is invalid',
                ], 500);
            }

            $difference = Carbon::now()->diffInSeconds($updatePassword->created_at);
            if ($difference > 900) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Fail_reset_password',
                    'message' => 'Token expired',
                ], 400);
            }

            User::where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

            DB::table('password_resets')->where(['email' => $request->email])->delete();

            return response()->json([
                'message' => 'Success! Your password has been changed!',
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'Fail_reset_password',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/auth/login",
     * summary="Sign in",
     * description="Login by email, password, company name",
     * operationId="authLogin",
     * tags={"UnAuthorize"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password", "company_name"},
     *       @OA\Property(property="email", type="string", pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *       @OA\Property(property="company_name", type="string", example="Omni"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="success", type="string", example="success"),
     *        @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *        @OA\Property(property="token", ref="#/components/schemas/BaseModel/properties/token"),
     *     )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="credentials_error"),
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address ,password or company name. Please try again")
     *        )
     *     )
     * )
     */
    public function login(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password', 'company_name');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'invalid_credentials',
                    'message' => 'The user credentials were incorrect. ',
                ], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_token',
                'message' => 'Sorry, wrong email address, password or company name. Please try again.',
            ], 422);
        }

        $user = JWTAuth::user();
        event(new LoginHistory($user));

        return response()->json([
            'status' => 'success',
            'user' => $this->getPermission($user),
            'token' => $token,
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/auth/register",
     * summary="Register a new user",
     * description="Register a new user. User should have admin permission",
     * operationId="authRegister",
     * tags={"User"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email", "name", "password", "company_name", "company_url", "company_consumer_key", "company_consumer_secret", "company_token", "company_token_secret", "scopes", "store_views", "roles"},
     *       @OA\Property(property="email", type="string", pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$", format="email", example="user1@mail.com"),
     *       @OA\Property(property="name", type="string", example="Johe Doe"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *       @OA\Property(property="company_name", type="string", example="Omni"),
     *       @OA\Property(property="company_url", type="string", example="https://omni.magento"),
     *       @OA\Property(property="company_consumer_key", ref="#/components/schemas/BaseModel/properties/token"),
     *       @OA\Property(property="company_consumer_secret", ref="#/components/schemas/BaseModel/properties/token"),
     *       @OA\Property(property="company_token", ref="#/components/schemas/BaseModel/properties/token"),
     *       @OA\Property(property="company_token_secret", ref="#/components/schemas/BaseModel/properties/token"),
     *       @OA\Property(
     *          property="scopes",
     *          type="array",
     *              @OA\Items(
     *                  type="object", ref="#/components/schemas/Scope"
     *          )
     *       ),
     *       @OA\Property(
     *          property="store_views",
     *          type="array",
     *              @OA\Items(
     *                  type="object", ref="#/components/schemas/StoreView"
     *          )
     *       ),
     *       @OA\Property(
     *          property="roles",
     *          type="array",
     *              @OA\Items(
     *                  type="object", ref="#/components/schemas/Role"
     *          )
     *       ),
     *       @OA\Property(property="is_admin", type="integer", example="1"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="success", type="string", example="success"),
     *        @OA\Property(property="new_user", type="object", ref="#/components/schemas/User"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
     */

    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|regex:/^.+@.+$/i',
                'name' => 'required|string',
                'password' => 'string',
                'company_name' => 'required|string',
                'scopes' => 'array',
                'store_views' => 'array',
                'roles' => 'array',
                'locations' => 'array',
                'is_admin' => 'numeric',
                'image_base_url' => 'string',
            ]);

            // Check if company was already registered
            // $companies = Company::all();
            // if (!in_array($request->company_name, array_column($companies->toArray(), 'name'))) {
            //     return response()->json([
            //         'status' => 'error',
            //         'error' => 'invalid_company_data',
            //         'message' => 'This company should be registered in advance.',
            //     ], 500);
            // }

            //Register a new user
            $newUser = new User();
            $newUser->email = $request->input('email');
            $newUser->name = $request->input('name');
            $newUser->company_name = $request->input('company_name');
            $newUser->is_admin = $request->input('is_admin');
            $newUser->password = bcrypt($request->input('password'));
            $newUser->save();

            //Register a new company
            $company = Company::where('name', $request->input('company_name'))->first();
            if ($company === null) {
                $newCompany = new Company();
                $newCompany->name = $request->input('company_name');
                $newCompany->url = $request->input('company_url');
                $newCompany->consumer_key = encrypt($request->input('company_consumer_key'));
                $newCompany->consumer_secret = encrypt($request->input('company_consumer_secret'));
                $newCompany->token = encrypt($request->input('company_token'));
                $newCompany->token_secret = encrypt($request->input('company_token_secret'));
                $newCompany->image_base_url = $request->input('image_base_url');
                $newCompany->save();
            }

            //Set user permissions
            foreach ($request->input('scopes') as $scope) {
                foreach ($request->input('store_views') as $store_view) {
                    foreach ($request->input('roles') as $role) {
                        $newUserPermission = new UserPermission();
                        $newUserPermission->user_id = $newUser->id;
                        $newUserPermission->scope_id = $scope;
                        $newUserPermission->store_view_id = $store_view;
                        $newUserPermission->role_id = $role;
                        $newUserPermission->save();
                    }
                }
            }

            //save userlocations
            foreach ($request->input('locations') as $location) {
                $newUserLocation = new UserLocation();
                $newUserLocation->user_id = $newUser->id;
                $newUserLocation->location_id = $location;
                $newUserLocation->save();
            }

            return response()->json([
                'status' => 'success',
                'new_user' => $this->getPermission($newUser),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'invalid_user_data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function me()
    {
        return response()->json($this->getPermission(auth()->user()));
    }

    /**
     * Log the user out ( Invalidate the token )
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get User log info
     */

    public function getLogs() {
        try {
            $result = User::where('is_admin', 0)
                ->get()
                ->makeHidden(['password', 'created_at', 'updated_at', 'is_admin']);
            return response()->json([
                'status' => 'success',
                'result' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_logs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
