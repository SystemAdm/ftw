<?php

namespace App\http\controllers\admin;

use App\http\controllers\Controller;
use App\models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RelationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relations = DB::table('guardian_user')
            ->leftJoin('users as guardians', 'guardian_user.guardian_id', '=', 'guardians.id')
            ->join('users as minors', 'guardian_user.minor_id', '=', 'minors.id')
            ->leftJoin('users as verifiers', 'guardian_user.verified_by', '=', 'verifiers.id')
            ->select([
                'guardian_user.*',
                'guardians.name as guardian_name',
                'guardians.email as guardian_email',
                'minors.name as minor_name',
                'minors.email as minor_email',
                'verifiers.name as verifier_name',
            ])
            ->latest('guardian_user.created_at')
            ->paginate(15);

        return Inertia::render('admin/relations/Index', [
            'relations' => $relations,
        ]);
    }

    /**
     * Store a newly created relation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'guardian_id' => 'required|exists:users,id',
            'minor_id' => 'required|exists:users,id',
            'relationship' => 'required|string|max:255',
        ]);

        if ($request->guardian_id == $request->minor_id) {
            return back()->withErrors(['guardian_id' => 'Guardian and minor cannot be the same user.']);
        }

        $exists = DB::table('guardian_user')
            ->where('guardian_id', $request->guardian_id)
            ->where('minor_id', $request->minor_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['guardian_id' => 'This relationship already exists.']);
        }

        DB::table('guardian_user')->insert([
            'guardian_id' => $request->guardian_id,
            'minor_id' => $request->minor_id,
            'relationship' => $request->relationship,
            'created_at' => now(),
            'updated_at' => now(),
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Relationship created and verified successfully.');
    }

    /**
     * Verify a relation.
     */
    public function verify(User $guardian, User $minor)
    {
        DB::table('guardian_user')
            ->where('guardian_id', $guardian->id)
            ->where('minor_id', $minor->id)
            ->update([
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

        return back()->with('success', 'Relationship verified successfully.');
    }

    /**
     * Remove the specified relation.
     */
    public function destroy(User $guardian, User $minor)
    {
        DB::table('guardian_user')
            ->where('guardian_id', $guardian->id)
            ->where('minor_id', $minor->id)
            ->delete();

        return back()->with('success', 'Relationship removed successfully.');
    }

    /**
     * Search for users to add as guardian or minor.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $term = $request->query('q');

        $users = User::query()
            ->select('id', 'name', 'email')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get();

        return response()->json(['data' => $users]);
    }
}
