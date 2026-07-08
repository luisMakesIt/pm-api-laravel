#!/usr/bin/env python3
import os, json, stat

BASE = "/root/projects/pm-api-laravel"
os.makedirs(BASE, exist_ok=True)

created = []
def w(path, content, mode=None):
    full = os.path.join(BASE, path)
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, 'w') as f:
        f.write(content.strip() + "\n")
    created.append(path)
    if mode:
        os.chmod(full, mode)

"""
=== Phase 2: Models ===
"""
w('app/Models/Project.php', r'''<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'git_repo_url',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    // ----- Accessors -----

    public function getProgresoAttribute(): float
    {
        $total = $this->requirements()->count();
        if ($total === 0) {
            return 0.0;
        }
        $completed = $this->requirements()->where('status', 'completado')->count();
        return round(($completed / $total) * 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planificacion' => 'Planning',
            'en_desarrollo' => 'In Development',
            'en_pruebas' => 'In Testing',
            'completado' => 'Completed',
            'cancelado' => 'Cancelled',
            default => $this->status,
        };
    }
}
''')

w('app/Models/Requirement.php', r'''<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'priority',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(RequirementActa::class, 'requirement_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    // ----- Accessors -----

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pendiente' => 'Pending',
            'en_progreso' => 'In Progress',
            'completado' => 'Completed',
            'rechazado' => 'Rejected',
            default => $this->status,
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'alta' => 'High',
            'media' => 'Medium',
            'baja' => 'Low',
            default => $this->priority,
        };
    }
}
''')

w('app/Models/RequirementActa.php', r'''<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementActa extends Model
{
    use HasFactory;

    protected $table = 'requirement_actas';

    protected $fillable = [
        'requirement_id',
        'fecha_sesion',
        'cliente_nombre',
        'cliente_email',
        'cliente_empresa',
        'participantes',
        'notas',
        'firmas',
        'acuerdos',
        'fecha_firma_acta',
        'estado_firma',
    ];

    protected $casts = [
        'fecha_sesion' => 'date',
        'fecha_firma_acta' => 'datetime',
        'participantes' => 'array',
        'acuerdos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }
}
''')

w('app/Models/Activity.php', r'''<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requirement_id',
        'title',
        'description',
        'status',
        'fecha_inicio_planificada',
        'fecha_limite',
        'tiempo_estimado_horas',
        'tiempo_real_horas',
        'asignado_a',
    ];

    protected $casts = [
        'fecha_inicio_planificada' => 'date',
        'fecha_limite' => 'date',
        'tiempo_estimado_horas' => 'float',
        'tiempo_real_horas' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class)->via('requirement');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function developmentLogs(): HasMany
    {
        return $this->hasMany(DevelopmentLog::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    // ----- Accessors -----

    public function getOverdueAttribute(): bool
    {
        if ($this->status === 'completada' || !$this->fecha_limite) {
            return false;
        }
        return now()->gt($this->fecha_limite);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pendiente' => 'Pending',
            'en_progreso' => 'In Progress',
            'completada' => 'Completed',
            'bloqueada' => 'Blocked',
            default => $this->status,
        };
    }

    public function getCompletionPercentageAttribute(): int
    {
        return match($this->status) {
            'pendiente' => 0,
            'en_progreso' => 50,
            'completada' => 100,
            'bloqueada' => 0,
            default => 0,
        };
    }
}
''')

w('app/Models/Product.php', r'''<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'name',
        'description',
        'type',
        'url_or_path',
        'version',
        'created_by',
        'created_at',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ----- Accessors -----

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'documento' => 'Document',
            'codigo' => 'Code',
            'diseno' => 'Design',
            'testcase' => 'Test Case',
            'configuracion' => 'Configuration',
            default => $this->type,
        };
    }
}
''')

w('app/Models/DevelopmentLog.php', r'''<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'developer_name',
        'developer_email',
        'tipo_accion',
        'descripcion',
        'tiempo_gastado_minutos',
        'fecha_registro',
        'link_o_ref',
        'developer_id',
    ];

    protected $casts = [
        'tiempo_gastado_minutos' => 'float',
        'fecha_registro' => 'datetime',
    ];

    // ----- Relationships -----

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    // ----- Accessors -----

    public function getTipoAccLabelAttribute(): string
    {
        return match($this->tipo_accion) {
            'commit' => 'Commit',
            'fix' => 'Fix',
            'feature' => 'Feature',
            'review' => 'Review',
            'deploy' => 'Deploy',
            default => $this->tipo_accion,
        };
    }
}
''')

w('app/Models/TeamMember.php', r'''<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'email',
        'role',
        'nivel_experiencia',
        'avatar_url',
        'estado',
        'joined_date',
        'git_username',
        'github_url',
        'dev_id',
    ];

    protected $casts = [
        'joined_date' => 'date',
    ];

    // ----- Relationships -----

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'dev_id');
    }

    // ----- Accessors -----

    public function getStatusLabelAttribute(): string
    {
        return match($this->estado) {
            'disponible' => 'Available',
            'en_tarea' => 'In Task',
            'ocupado' => 'Busy',
            'fuera' => 'Away',
            default => $this->estado,
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'developer' => 'Developer',
            'designer' => 'Designer',
            'tester' => 'Tester',
            'tech_lead' => 'Tech Lead',
            default => $this->role,
        };
    }
}
''')

w('app/Models/User.php', r'''<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_url',
        'github_username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ----- Relationships -----

    public function assignedActivities()
    {
        return $this->hasMany(Activity::class, 'asignado_a');
    }

    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function developmentLogs()
    {
        return $this->hasMany(DevelopmentLog::class, 'developer_id');
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class, 'dev_id');
    }
}
''')

print("[+] All 8 models created")

"""
=== Phase 2b: Controllers ===
"""

w('app/Http/Controllers/Api/BaseController.php', r'''<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    //
}
''')

w('app/Http/Controllers/Api/ActivityController.php', r'''<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\Requirement;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request, Requirement $requirement): JsonResponse
    {
        if ($request->filled('page')) {
            $perPage = $request->input('per_page', 15);
            $activities = $requirement->activities()->with(['assignedTo', 'products', 'developmentLogs'])->paginate($perPage);
        } else {
            $activities = $requirement->activities()->with(['assignedTo', 'products', 'developmentLogs'])->get();
        }

        return response()->json([
            'success' => true,
            'data' => $activities,
            'meta' => isset($activities->total) ? [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ] : [],
        ]);
    }

    public function store(Request $request, Requirement $requirement): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pendiente,en_progreso,completada,bloqueada',
            'fecha_inicio_planificada' => 'nullable|date',
            'fecha_limite' => 'nullable|date',
            'tiempo_estimado_horas' => 'nullable|numeric|min:0',
            'tiempo_real_horas' => 'nullable|numeric|min:0',
            'asignado_a' => 'nullable|exists:users,id',
        ]);

        $data['requirement_id'] = $requirement->id;
        $data['status'] = $data['status'] ?? 'pendiente';

        $activity = $requirement->activities()->create($data);
        $activity->load(['assignedTo', 'products', 'developmentLogs']);

        return response()->json([
            'success' => true,
            'message' => 'Activity created',
            'data' => $activity,
        ], 201);
    }

    public function show(Requirement $requirement, Activity $activity): JsonResponse
    {
        if ($activity->requirement_id !== $requirement->id) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $activity->load(['requirement.project', 'assignedTo', 'products', 'developmentLogs']);

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    public function update(Request $request, Requirement $requirement, Activity $activity): JsonResponse
    {
        if ($activity->requirement_id !== $requirement->id) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pendiente,en_progreso,completada,bloqueada',
            'fecha_inicio_planificada' => 'nullable|date',
            'fecha_limite' => 'nullable|date',
            'tiempo_estimado_horas' => 'nullable|numeric|min:0',
            'tiempo_real_horas' => 'nullable|numeric|min:0',
            'asignado_a' => 'nullable|exists:users,id',
        ]);

        $activity->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Activity updated',
            'data' => $activity->fresh(['assignedTo', 'products', 'developmentLogs']),
        ]);
    }

    public function destroy(Requirement $requirement, Activity $activity): JsonResponse
    {
        if ($activity->requirement_id !== $requirement->id) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity deleted',
        ]);
    }

    public function updateStatus(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pendiente,en_progreso,completada,bloqueada',
        ]);

        $activity->update(['status' => $data['status']]);

        return response()->json([
            'success' => true,
            'data' => $activity->fresh(),
        ]);
    }

    public function updateTime(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'tiempo_estimado_horas' => 'nullable|numeric|min:0',
            'tiempo_real_horas' => 'nullable|numeric|min:0',
        ]);

        $activity->update($data);

        return response()->json([
            'success' => true,
            'data' => $activity->fresh(),
        ]);
    }
}
''')

w('app/Http/Controllers/Api/ProductController.php', r'''<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Activity $activity): JsonResponse
    {
        $products = $activity->products()->with('createdBy')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function store(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:documento,codigo,diseno,testcase,configuracion',
            'url_or_path' => 'nullable|string|max:500',
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $data['activity_id'] = $activity->id;
        $data['created_by'] = optional(auth()->user())->id ?? $data['created_by'];

        $product = $activity->products()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created',
            'data' => $product->load('createdBy'),
        ], 201);
    }

    public function show(Activity $activity, Product $product): JsonResponse
    {
        if ($product->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product->load('createdBy'),
        ]);
    }

    public function update(Request $request, Activity $activity, Product $product): JsonResponse
    {
        if ($product->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:documento,codigo,diseno,testcase,configuracion',
            'url_or_path' => 'nullable|string|max:500',
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $product->update($data);

        return response()->json([
            'success' => true,
            'data' => $product->fresh(),
        ]);
    }

    public function destroy(Activity $activity, Product $product): JsonResponse
    {
        if ($product->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted',
        ]);
    }
}
''')

w('app/Http/Controllers/Api/DevelopmentLogController.php', r'''<?php

namespace App\Http\Controllers\Api;

use App\Models\DevelopmentLog;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevelopmentLogController extends Controller
{
    public function index(Activity $activity): JsonResponse
    {
        $logs = $activity->developmentLogs()->with('developer')->orderBy('fecha_registro', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function store(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'developer_name' => 'required|string|max:255',
            'developer_email' => 'required|email|max:255',
            'tipo_accion' => 'required|in:commit,fix,feature,review,deploy',
            'descripcion' => 'required|string',
            'tiempo_gastado_minutos' => 'required|numeric|min:0',
            'fecha_registro' => 'nullable|date',
            'link_o_ref' => 'nullable|string|max:500',
            'developer_id' => 'nullable|exists:users,id',
        ]);

        $data['activity_id'] = $activity->id;
        $user = auth()->user();
        if (!$data['developer_id'] && $user) {
            $data['developer_id'] = $user->id;
            if (!$data['developer_name']) {
                $data['developer_name'] = $user->name;
            }
            if (!$data['developer_email']) {
                $data['developer_email'] = $user->email;
            }
        }
        if (!$data['fecha_registro']) {
            $data['fecha_registro'] = now()->format('Y-m-d');
        }

        $log = $activity->developmentLogs()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Dev log created',
            'data' => $log,
        ], 201);
    }

    public function show(Activity $activity, DevelopmentLog $log): JsonResponse
    {
        if ($log->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    public function update(Request $request, Activity $activity, DevelopmentLog $log): JsonResponse
    {
        if ($log->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        $data = $request->validate([
            'developer_name' => 'sometimes|required|string|max:255',
            'developer_email' => 'sometimes|required|email|max:255',
            'tipo_accion' => 'sometimes|required|in:commit,fix,feature,review,deploy',
            'descripcion' => 'sometimes|required|string',
            'tiempo_gastado_minutos' => 'sometimes|numeric|min:0',
            'fecha_registro' => 'nullable|date',
            'link_o_ref' => 'nullable|string|max:500',
            'developer_id' => 'nullable|exists:users,id',
        ]);

        $log->update($data);

        return response()->json([
            'success' => true,
            'data' => $log->fresh(),
        ]);
    }

    public function destroy(Activity $activity, DevelopmentLog $log): JsonResponse
    {
        if ($log->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dev log deleted',
        ]);
    }
}
''')

w('app/Http/Controllers/Api/TeamMemberController.php', r'''<?php

namespace App\Http\Controllers\Api;

use App\Models\TeamMember;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $members = $project->teamMembers()->get();

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:developer,designer,tester,tech_lead',
            'nivel_experiencia' => 'nullable|in:junior,middle,senior,lead',
            'avatar_url' => 'nullable|string|max:500',
            'estado' => 'nullable|in:disponible,en_tarea,ocupado,fuera',
            'joined_date' => 'nullable|date',
            'git_username' => 'nullable|string|max:100',
            'github_url' => 'nullable|url|max:500',
            'dev_id' => 'nullable|exists:users,id',
        ]);

        $data['project_id'] = $project->id;
        $data['estado'] = $data['estado'] ?? 'disponible';

        $member = $project->teamMembers()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Team member added',
            'data' => $member,
        ], 201);
    }

    public function show(Project $project, TeamMember $member): JsonResponse
    {
        if ($member->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }

    public function update(Request $request, Project $project, TeamMember $member): JsonResponse
    {
        if ($member->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'role' => 'sometimes|required|in:developer,designer,tester,tech_lead',
            'nivel_experiencia' => 'sometimes|in:junior,middle,senior,lead',
            'avatar_url' => 'nullable|string|max:500',
            'estado' => 'sometimes|in:disponible,en_tarea,ocupado,fuera',
            'joined_date' => 'nullable|date',
            'git_username' => 'nullable|string|max:100',
            'github_url' => 'nullable|url|max:500',
            'dev_id' => 'nullable|exists:users,id',
        ]);

        $member->update($data);

        return response()->json([
            'success' => true,
            'data' => $member->fresh(),
        ]);
    }

    public function destroy(Project $project, TeamMember $member): JsonResponse
    {
        if ($member->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team member removed',
        ]);
    }
}
''')

w('app/Http/Controllers/Api/HealthController.php', r'''<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'PM API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'New token generated',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
''')

print("[+] All controllers created")

"""
=== Phase 3: Database Migrations ===
"""

w('database/migrations/0001_01_01_000000_create_users_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('avatar_url')->nullable();
            $table->string('github_username')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
''')

w('database/migrations/0001_01_01_000001_create_cache_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
''')

w('database/migrations/0001_01_01_000002_create_jobs_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
    }
};
''')

w('database/migrations/0001_01_01_000003_create_sanctum_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
''')

w('database/migrations/0002_01_01_000004_create_projects_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('git_repo_url')->nullable();
            $table->string('status')->default('planificacion');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
''')

w('database/migrations/0002_02_01_000005_create_requirements_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('media');
            $table->string('status')->default('pendiente');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
''')

w('database/migrations/0002_03_01_000006_create_requirement_actas_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_actas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->date('fecha_sesion')->nullable();
            $table->string('cliente_nombre');
            $table->string('cliente_email');
            $table->string('cliente_empresa');
            $table->json('participantes')->nullable();
            $table->text('notas')->nullable();
            $table->text('firmas')->nullable();
            $table->json('acuerdos')->nullable();
            $table->date('fecha_firma_acta')->nullable();
            $table->string('estado_firma')->default('sin_firmar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_actas');
    }
};
''')

w('database/migrations/0002_04_01_000007_create_activities_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pendiente');
            $table->date('fecha_inicio_planificada')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->decimal('tiempo_estimado_horas', 8, 2)->default(0);
            $table->decimal('tiempo_real_horas', 8, 2)->default(0);
            $table->unsignedBigInteger('asignado_a')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
''')

w('database/migrations/0002_05_01_000008_create_products_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('url_or_path')->nullable();
            $table->string('version')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
''')

w('database/migrations/0002_06_01_000009_create_development_logs_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->string('developer_name');
            $table->string('developer_email');
            $table->string('tipo_accion');
            $table->text('descripcion');
            $table->decimal('tiempo_gastado_minutos', 8, 2)->default(0);
            $table->date('fecha_registro');
            $table->string('link_o_ref')->nullable();
            $table->unsignedBigInteger('developer_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_logs');
    }
};
''')

w('database/migrations/0002_07_01_000010_create_team_members_table.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->string('nivel_experiencia')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('estado')->default('disponible');
            $table->date('joined_date')->nullable();
            $table->string('git_username')->nullable();
            $table->string('github_url')->nullable();
            $table->unsignedBigInteger('dev_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
''')

w('database/migrations/0003_01_01_000011_add_indexes.php', r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index on project_id in requirements, activities, development_logs, team_members
        Schema::table('requirements', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->index('status');
            $table->index('fecha_limite');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->index('estado');
            $table->index('role');
        });

        Schema::table('requirement_actas', function (Blueprint $table) {
            $table->index('estado_firma');
            $table->index('fecha_sesion');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('development_logs', function (Blueprint $table) {
            $table->index('tipo_accion');
            $table->index('fecha_registro');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('type');
        });
    }

    public function down(): void
    {
        // Indexes are automatically dropped when migrations rollback
    }
};
''')

print("[+] All migrations done")

print(f"\n=== Total files written: {len(created)} ===")
