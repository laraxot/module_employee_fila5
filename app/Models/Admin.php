<?php

declare(strict_types=1);

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Modules\Gdpr\Models\Consent;
use Modules\Gdpr\Models\Treatment;
use Modules\Media\Models\Media;
use Modules\TechPlanner\Models\Profile;
use Modules\User\Models\AuthenticationLog;
use Modules\User\Models\Device;
use Modules\User\Models\DeviceUser;
use Modules\User\Models\Notification;
use Modules\User\Models\OauthClient;
use Modules\User\Models\OauthToken;
use Modules\User\Models\Permission;
use Modules\User\Models\Role;
use Modules\User\Models\SocialiteUser;
use Modules\User\Models\Team;
use Modules\User\Models\TeamUser;
use Modules\User\Models\Tenant;
use Modules\User\Models\TenantUser;
use Override;
use Parental\HasParent;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * Class Admin
 *
 * NOTA: Il trait HasFactory è stato rimosso perché già incluso nella catena di ereditarietà (BaseUser -> User -> Admin).
 * Dichiararlo qui è ridondante e può causare warning o confusione.
 * Vedi docs/DRY-model-traits.md
 *
 * @property-read Collection<int, Consent> $activeConsents
 * @property-read int|null $active_consents_count
 * @property-read Collection<int, AuthenticationLog> $authentications
 * @property-read int|null $authentications_count
 * @property-read Collection<int, OauthClient> $clients
 * @property-read int|null $clients_count
 * @property-read Collection<int, Consent> $consents
 * @property-read int|null $consents_count
 * @property-read Team|null $currentTeam
 * @property-read TenantUser|TeamUser|DeviceUser|null $pivot
 * @property-read Collection<int, Device> $devices
 * @property-read int|null $devices_count
 * @property-read Collection<int, User> $all_team_users
 * @property-read string $full_name
 * @property-read string $name
 * @property-read AuthenticationLog|null $latestAuthentication
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Team> $membershipTeams
 * @property-read int|null $membership_teams_count
 * @property-read DatabaseNotificationCollection<int, Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, OauthClient> $oauthApps
 * @property-read int|null $oauth_apps_count
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Profile|null $profile
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-write mixed $password
 * @property-read Collection<int, SocialiteUser> $socialiteUsers
 * @property-read int|null $socialite_users_count
 * @property-read Collection<int, TeamUser> $teamUsers
 * @property-read int|null $team_users_count
 * @property-read Collection<int, Tenant> $tenants
 * @property-read int|null $tenants_count
 * @property-read Collection<int, OauthToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, Treatment> $treatments
 * @property-read int|null $treatments_count
 *
 * @method static Builder<static>|Admin childrenWith(array<int, string> $relations)
 * @method static Builder<static>|Admin childrenWithCount(array<int, string> $relations)
 * @method static \Modules\Employee\Database\Factories\AdminFactory factory($count = null, $state = [])
 * @method static Builder<static>|Admin newModelQuery()
 * @method static Builder<static>|Admin newQuery()
 * @method static Builder<static>|Admin orWhereNotState(string $column, $states)
 * @method static Builder<static>|Admin orWhereState(string $column, $states)
 * @method static Builder<static>|Admin permission($permissions, bool $without = false)
 * @method static Builder<static>|Admin query()
 * @method static Builder<static>|Admin role($roles, ?string $guard = null, bool $without = false)
 * @method static Builder<static>|Admin team($teams, bool $without = false)
 * @method static Builder<static>|Admin whereNotState(string $column, $states)
 * @method static Builder<static>|Admin whereState(string $column, $states)
 * @method static Builder<static>|Admin withoutPermission($permissions)
 * @method static Builder<static>|Admin withoutRole($roles, ?string $guard = null)
 * @method static Builder<static>|Admin withoutTeam($teams)
 *
 * @property string $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property Carbon|null $deleted_at
 * @property string|null $lang
 * @property bool $is_active
 * @property bool $is_otp
 * @property Carbon|null $password_expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property string|null $deleted_by
 * @property string|null $type
 * @property string|null $state
 *
 * @method static Builder<static>|Admin whereCreatedAt($value)
 * @method static Builder<static>|Admin whereCreatedBy($value)
 * @method static Builder<static>|Admin whereCurrentTeamId($value)
 * @method static Builder<static>|Admin whereDeletedAt($value)
 * @method static Builder<static>|Admin whereDeletedBy($value)
 * @method static Builder<static>|Admin whereEmail($value)
 * @method static Builder<static>|Admin whereEmailVerifiedAt($value)
 * @method static Builder<static>|Admin whereFirstName($value)
 * @method static Builder<static>|Admin whereId($value)
 * @method static Builder<static>|Admin whereIsActive($value)
 * @method static Builder<static>|Admin whereIsOtp($value)
 * @method static Builder<static>|Admin whereLang($value)
 * @method static Builder<static>|Admin whereLastName($value)
 * @method static Builder<static>|Admin whereName($value)
 * @method static Builder<static>|Admin wherePassword($value)
 * @method static Builder<static>|Admin wherePasswordExpiresAt($value)
 * @method static Builder<static>|Admin whereProfilePhotoPath($value)
 * @method static Builder<static>|Admin whereRememberToken($value)
 * @method static Builder<static>|Admin whereTwoFactorConfirmedAt($value)
 * @method static Builder<static>|Admin whereTwoFactorRecoveryCodes($value)
 * @method static Builder<static>|Admin whereTwoFactorSecret($value)
 * @method static Builder<static>|Admin whereType($value)
 * @method static Builder<static>|Admin whereUpdatedAt($value)
 * @method static Builder<static>|Admin whereUpdatedBy($value)
 *
 * @mixin \Eloquent
 */
class Admin extends User
{
    use HasParent;

    /**
     * Gli attributi che sono mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'address',
        'phone',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return array_merge(
            parent::casts(),
            [
                // 'certifications' => 'array',
                // 'availability' => 'array',
            ],
        );
    }
}
