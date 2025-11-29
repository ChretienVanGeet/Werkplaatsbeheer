<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class WorkflowGroupScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $userGroupIds = $user->groups()->pluck('groups.id');
        $selectedGroupId = session('selected_group_id');

        // If a specific group is selected, filter by that group only (if user has access)
        if (is_int($selectedGroupId) && $userGroupIds->contains($selectedGroupId)) {
            $this->applySelectedGroupFilter($builder, $selectedGroupId);

            return;
        }

        // Otherwise, apply the standard workflow group filtering based on subjects
        $builder->where(function (Builder $query) use ($userGroupIds) {
            // Activities - with groups user has access to OR with no groups
            $query->orWhere(function (Builder $subQuery) use ($userGroupIds) {
                $subQuery->where('subject_type', 'App\\Models\\Activity')
                    ->where(function (Builder $activityQuery) use ($userGroupIds) {
                        // Activities with no groups (visible to everyone)
                        $activityQuery->whereIn('subject_id', function ($noGroupQuery) {
                            $noGroupQuery->select('activities.id')
                                ->from('activities')
                                ->leftJoin('group_activity', 'activities.id', '=', 'group_activity.activity_id')
                                ->whereNull('group_activity.activity_id');
                        });

                        // OR activities with groups the user has access to (if user has groups)
                        if ($userGroupIds->isNotEmpty()) {
                            $activityQuery->orWhereIn('subject_id', function ($groupQuery) use ($userGroupIds) {
                                $groupQuery->select('activities.id')
                                    ->from('activities')
                                    ->join('group_activity', 'activities.id', '=', 'group_activity.activity_id')
                                    ->whereIn('group_activity.group_id', $userGroupIds);
                            });
                        }
                    });
            })
            // Companies - with groups user has access to OR with no groups
            ->orWhere(function (Builder $subQuery) use ($userGroupIds) {
                $subQuery->where('subject_type', 'App\\Models\\Company')
                    ->where(function (Builder $companyQuery) use ($userGroupIds) {
                        // Companies with no groups (visible to everyone)
                        $companyQuery->whereIn('subject_id', function ($noGroupQuery) {
                            $noGroupQuery->select('companies.id')
                                ->from('companies')
                                ->leftJoin('group_company', 'companies.id', '=', 'group_company.company_id')
                                ->whereNull('group_company.company_id');
                        });

                        // OR companies with groups the user has access to (if user has groups)
                        if ($userGroupIds->isNotEmpty()) {
                            $companyQuery->orWhereIn('subject_id', function ($groupQuery) use ($userGroupIds) {
                                $groupQuery->select('companies.id')
                                    ->from('companies')
                                    ->join('group_company', 'companies.id', '=', 'group_company.company_id')
                                    ->whereIn('group_company.group_id', $userGroupIds);
                            });
                        }
                    });
            })
            // Participants - with groups user has access to OR with no groups
            ->orWhere(function (Builder $subQuery) use ($userGroupIds) {
                $subQuery->where('subject_type', 'App\\Models\\Participant')
                    ->where(function (Builder $participantQuery) use ($userGroupIds) {
                        // Participants with no groups (visible to everyone)
                        $participantQuery->whereIn('subject_id', function ($noGroupQuery) {
                            $noGroupQuery->select('participants.id')
                                ->from('participants')
                                ->leftJoin('group_participant', 'participants.id', '=', 'group_participant.participant_id')
                                ->whereNull('group_participant.participant_id');
                        });

                        // OR participants with groups the user has access to (if user has groups)
                        if ($userGroupIds->isNotEmpty()) {
                            $participantQuery->orWhereIn('subject_id', function ($groupQuery) use ($userGroupIds) {
                                $groupQuery->select('participants.id')
                                    ->from('participants')
                                    ->join('group_participant', 'participants.id', '=', 'group_participant.participant_id')
                                    ->whereIn('group_participant.group_id', $userGroupIds);
                            });
                        }
                    });
            });
        });
    }

    private function applySelectedGroupFilter(Builder $builder, int $selectedGroupId): void
    {
        $builder->where(function (Builder $query) use ($selectedGroupId) {
            // Activities - only those associated with the selected group
            $query->orWhere(function (Builder $subQuery) use ($selectedGroupId) {
                $subQuery->where('subject_type', 'App\\Models\\Activity')
                    ->whereIn('subject_id', function ($groupQuery) use ($selectedGroupId) {
                        $groupQuery->select('activities.id')
                            ->from('activities')
                            ->join('group_activity', 'activities.id', '=', 'group_activity.activity_id')
                            ->where('group_activity.group_id', $selectedGroupId);
                    });
            })
            // Companies - only those associated with the selected group
            ->orWhere(function (Builder $subQuery) use ($selectedGroupId) {
                $subQuery->where('subject_type', 'App\\Models\\Company')
                    ->whereIn('subject_id', function ($groupQuery) use ($selectedGroupId) {
                        $groupQuery->select('companies.id')
                            ->from('companies')
                            ->join('group_company', 'companies.id', '=', 'group_company.company_id')
                            ->where('group_company.group_id', $selectedGroupId);
                    });
            })
            // Participants - only those associated with the selected group
            ->orWhere(function (Builder $subQuery) use ($selectedGroupId) {
                $subQuery->where('subject_type', 'App\\Models\\Participant')
                    ->whereIn('subject_id', function ($groupQuery) use ($selectedGroupId) {
                        $groupQuery->select('participants.id')
                            ->from('participants')
                            ->join('group_participant', 'participants.id', '=', 'group_participant.participant_id')
                            ->where('group_participant.group_id', $selectedGroupId);
                    });
            });
        });
    }
}
