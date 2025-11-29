<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserHasGroupAccess;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GroupAccessMiddlewareTest extends TestCase
{
    private EnsureUserHasGroupAccess $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new EnsureUserHasGroupAccess();
    }

    public function test_unauthenticated_user_is_denied(): void
    {
        $request = Request::create('/test');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Unauthorized');

        $this->middleware->handle($request, function () {
            return response('OK');
        });
    }

    public function test_user_without_groups_is_denied(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $request = Request::create('/test');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('You do not belong to any groups');

        $this->middleware->handle($request, function () {
            return response('OK');
        });
    }

    public function test_user_with_groups_can_access_without_model_type(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Auth::login($user);

        $request = Request::create('/test');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_user_can_access_activity_in_their_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $activity = Activity::factory()->create();

        $user->groups()->attach($group);
        $activity->groups()->attach($group);

        Auth::login($user);

        $request = Request::create('/activities/'.$activity->id);
        $request->setRouteResolver(function () use ($activity, $request) {
            $route = new \Illuminate\Routing\Route('GET', '/activities/{activity}', []);
            $route->bind($request);
            $route->setParameter('activity', $activity->id);

            return $route;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'activity');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_cannot_access_activity_not_in_their_group(): void
    {
        $user = User::factory()->create();
        $userGroup = Group::factory()->create();
        $otherGroup = Group::factory()->create();
        $activity = Activity::factory()->create();

        $user->groups()->attach($userGroup);
        $activity->groups()->attach($otherGroup);

        Auth::login($user);

        $request = Request::create('/activities/'.$activity->id);
        $request->setRouteResolver(function () use ($activity, $request) {
            $route = new \Illuminate\Routing\Route('GET', '/activities/{activity}', []);
            $route->bind($request);
            $route->setParameter('activity', $activity->id);

            return $route;
        });

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('You do not have access to this resource');

        $this->middleware->handle($request, function () {
            return response('OK');
        }, 'activity');
    }

    public function test_user_can_access_company_in_their_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $company = Company::factory()->create();

        $user->groups()->attach($group);
        $company->groups()->attach($group);

        Auth::login($user);

        $request = Request::create('/companies/'.$company->id);
        $request->setRouteResolver(function () use ($company, $request) {
            $route = new \Illuminate\Routing\Route('GET', '/companies/{company}', []);
            $route->bind($request);
            $route->setParameter('company', $company->id);

            return $route;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'company');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_can_access_participant_in_their_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $participant = Participant::factory()->create();

        $user->groups()->attach($group);
        $participant->groups()->attach($group);

        Auth::login($user);

        $request = Request::create('/participants/'.$participant->id);
        $request->setRouteResolver(function () use ($participant, $request) {
            $route = new \Illuminate\Routing\Route('GET', '/participants/{participant}', []);
            $route->bind($request);
            $route->setParameter('participant', $participant->id);

            return $route;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'participant');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_can_access_group_they_belong_to(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $otherGroup = Group::factory()->create();

        $user->groups()->attach($group);

        Auth::login($user);

        $request = Request::create('/groups/'.$group->id);
        $request->setRouteResolver(function () use ($group, $request) {
            $route = new \Illuminate\Routing\Route('GET', '/groups/{group}', []);
            $route->bind($request);
            $route->setParameter('group', $group->id);

            return $route;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'group');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_adds_user_group_ids_to_request(): void
    {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        $user->groups()->attach([$group1->id, $group2->id]);

        Auth::login($user);

        $request = Request::create('/test');

        $this->middleware->handle($request, function ($req) use ($group1, $group2) {
            $userGroupIds = $req->get('userGroupIds');
            $this->assertNotNull($userGroupIds);
            $this->assertTrue($userGroupIds->contains($group1->id));
            $this->assertTrue($userGroupIds->contains($group2->id));

            return response('OK');
        });
    }
}
