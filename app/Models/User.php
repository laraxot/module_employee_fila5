<?php

declare(strict_types=1);

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Modules\Gdpr\Models\Consent;
use Modules\Gdpr\Models\Traits\HasGdpr;
use Modules\Gdpr\Models\Treatment;
use Modules\Media\Models\Media;
use Modules\TechPlanner\Models\Profile;
use Modules\User\Models\AuthenticationLog;
use Modules\User\Models\BaseUser;
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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\HasStatesContract;

/**
 * Employee Module User Model
 *
 * Extends BaseUser with Single Table Inheritance for Employee module.
 * Parent class for Admin and Employee models using Parental STI.
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
 * @method static Builder<static>|User childrenWith(array<int, string> $relations)
 * @method static Builder<static>|User childrenWithCount(array<int, string> $relations)
 * @method static \Modules\Employee\Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User orWhereNotState(string $column, $states)
 * @method static Builder<static>|User orWhereState(string $column, $states)
 * @method static Builder<static>|User permission($permissions, bool $without = false)
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static Builder<static>|User team($teams, bool $without = false)
 * @method static Builder<static>|User whereNotState(string $column, $states)
 * @method static Builder<static>|User whereState(string $column, $states)
 * @method static Builder<static>|User withoutPermission($permissions)
 * @method static Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static Builder<static>|User withoutTeam($teams)
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
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereCreatedBy($value)
 * @method static Builder<static>|User whereCurrentTeamId($value)
 * @method static Builder<static>|User whereDeletedAt($value)
 * @method static Builder<static>|User whereDeletedBy($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereFirstName($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereIsActive($value)
 * @method static Builder<static>|User whereIsOtp($value)
 * @method static Builder<static>|User whereLang($value)
 * @method static Builder<static>|User whereLastName($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePasswordExpiresAt($value)
 * @method static Builder<static>|User whereProfilePhotoPath($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static Builder<static>|User whereTwoFactorSecret($value)
 * @method static Builder<static>|User whereType($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User whereUpdatedBy($value)
 *
 * @mixin \Eloquent
 */
class User extends BaseUser implements HasMedia, HasStatesContract
{
    use HasGdpr;
    use HasStates;
    use InteractsWithMedia;
}
