@extends($theme.'layouts.app')
@section('title',trans('Home'))

@section('content')
    <!-- wrapper -->
    <div class="wrapper" id="getMatchList" v-cloak>
       
        <!-- leftbar -->
        <div class="leftbar" id="leftbar">
            <div class="px-1 mt-2 d-lg-none">
                <button
                    class="remove-class-btn light btn-custom"
                    onclick="removeClass('leftbar')"
                >
                    <i class="fal fa-chevron-left"></i> @lang('Back')
                </button>
            </div>
            @include($theme.'partials.home.leftMenu')
        </div>

        <!-- contents -->
        <div class="content">
            @include($theme.'partials.home.slider')
            @include($theme.'partials.home.navbar')
            @if(Request::routeIs('match'))
                @include($theme.'partials.home.match')
            @else
                @include($theme.'partials.home.content')
            @endif

            {{-- SCOREBOARD SECTION --}}
            <div class="container-fluid mt-4">
                @isset($selectedCategory)
                    @if($selectedCategory)
                        <div class="tournament-header mb-4 ms-2">
                            <div class="d-flex align-items-center justify-content-start">
                                <!-- <img src="{{ asset($selectedCategory->image ?? 'images/default_trophy.png') }}" 
                                     alt="{{ $selectedCategory->icon }}" 
                                     class="trophy-img me-3"> -->
                                    <span class="me-2 cat-icons">{!! $selectedCategory->icon !!}</span>
                                <div>
                                    <h2 class="tournament-title mb-0">{{ $selectedCategory->name }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap">
                            @foreach($selectedCategory->gameSchedule as $schedule)
                                @php
                                    // Filter out matches without date/time and sort by date
                                    $matches = $schedule->gameMatch
                                        ->filter(function($match) {
                                            return !is_null($match->match_date) && !is_null($match->match_time);
                                        })
                                        ->sortBy('match_date');
                                    
                                    // Find the most relevant match to display
                                    $now = now();
                                    $upcomingMatch = null;
                                    $latestCompletedMatch = null;
                                    $currentMatch = null;

                                    foreach ($matches as $match) {
                                        $matchTime = \Carbon\Carbon::parse($match->match_date . ' ' . $match->match_time);
                                        
                                        if ($match->winner_id) {
                                            $latestCompletedMatch = $match;
                                        } elseif ($matchTime->gte($now)) {
                                            $upcomingMatch = $upcomingMatch ?? $match;
                                        }
                                    }

                                    // Priority: 1) Latest completed match, 2) Next upcoming match, 3) Most recent past match
                                    $currentMatch = $latestCompletedMatch ?? $upcomingMatch ?? $matches->last();
                                @endphp

                                @if($currentMatch)
                                    @php
                                        $matchTime = \Carbon\Carbon::parse($currentMatch->match_date . ' ' . $currentMatch->match_time);
                                        $matchEndTime = $matchTime->copy()->addHours(2); // Assuming 2 hour match duration
                                        
                                        $isCompleted = (bool)$currentMatch->winner_id;
                                        $isLive = !$isCompleted && $now->between($matchTime, $matchEndTime);
                                        $isPast = !$isCompleted && $now->gt($matchEndTime);
                                        
                                        $statusClass = $isCompleted ? 'completed' : ($isLive ? 'live' : ($isPast ? 'Not-Started' : 'scheduled'));
                                        $statusText = $isCompleted ? 'Completed' : ($isLive ? 'Live' : ($isPast ? 'Not-Started' : 'Scheduled'));
                                    @endphp

                                    <div class="col-lg-4 mb-4 p-2">
                                        <div class="match-card">
                                            <div class="match-header">
                                                <h4 class="match-title">{{ $schedule->name }}</h4>
                                                <div class="match-status-badge {{ $statusClass }}">
                                                    {{ $statusText }}
                                                </div>
                                            </div>
                                            
                                            <div class="match-body">
                                                <!-- Team A -->
                                                <div class="team team-a {{ $currentMatch->winner_id == $currentMatch->team1_id ? 'winner' : '' }}">
                                                    <div class="team-logo">
                                                        @if($currentMatch->team1)
                                                            <img src="{{ asset($currentMatch->team1->avatar ?? 'assets/upload/team/default-img.webp') }}" 
                                                                alt="{{ $currentMatch->team1->name }}">
                                                        @else
                                                            <div class="team-initials">BYE</div>
                                                        @endif
                                                    </div>
                                                    <div class="team-info">
                                                        <h5 class="team-name">
                                                            {{ $currentMatch->team1 ? $currentMatch->team1->name : ($currentMatch->team1_placeholder ?? 'BYE') }}
                                                        </h5>
                                                        @if($currentMatch->team1_score !== null)
                                                            <div class="team-score">{{ $currentMatch->team1_score }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Match Info -->
                                                <div class="match-info">
                                                    <div class="match-round">Round {{ $currentMatch->round }}</div>
                                                    <div class="vs-circle">VS</div>
                                                    <div class="match-time">
                                                        {{ $matchTime->format('M d, H:i') }}
                                                    </div>
                                                </div>
                                                
                                                <!-- Team B -->
                                                <div class="team team-b {{ $currentMatch->winner_id == $currentMatch->team2_id ? 'winner' : '' }}">
                                                    <div class="team-info">
                                                        <h5 class="team-name">
                                                            {{ $currentMatch->team2 ? $currentMatch->team2->name : ($currentMatch->team2_placeholder ?? 'BYE') }}
                                                        </h5>
                                                        @if($currentMatch->team2_score !== null)
                                                            <div class="team-score">{{ $currentMatch->team2_score }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="team-logo">
                                                        @if($currentMatch->team2)
                                                            <img src="{{ asset($currentMatch->team2->avatar ?? 'assets/upload/team/default-img.webp') }}" 
                                                                alt="{{ $currentMatch->team2->name }}">
                                                        @else
                                                            <div class="team-initials">BYE</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @if($currentMatch->winner_id)
                                                <div class="match-footer">
                                                    <div class="winner-announcement">
                                                        <i class="fas fa-trophy"></i>
                                                        {{ $currentMatch->winner->name }} won by 
                                                        @if(is_numeric($currentMatch->team1_score) && is_numeric($currentMatch->team2_score))
                                                            {{ abs($currentMatch->team1_score - $currentMatch->team2_score) }} points
                                                        @else
                                                            default
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if($matches->count() > 1)
                                                <button class="btn btn-outline-primary view-more-matches" 
                                                        data-schedule="{{ $schedule->id }}">
                                                    View All Matches <i class="fas fa-chevron-down"></i>
                                                </button>
                                                
                                                <div class="all-matches-container" id="all-matches-{{ $schedule->id }}" style="display:none;">
                                                    @foreach($matches as $match)
                                                        @php
                                                            $mTime = \Carbon\Carbon::parse($match->match_date . ' ' . $match->match_time);
                                                            $mEndTime = $mTime->copy()->addHours(2);
                                                            $mIsCompleted = (bool)$match->winner_id;
                                                            $mIsLive = !$mIsCompleted && $now->between($mTime, $mEndTime);
                                                            $mIsPast = !$mIsCompleted && $now->gt($mEndTime);
                                                            
                                                            $mStatusClass = $mIsCompleted ? 'completed' : ($mIsLive ? 'live' : ($mIsPast ? 'Not-Started' : 'scheduled'));
                                                        @endphp
                                                        
                                                        <div class="match-mini-card {{ $match->id == $currentMatch->id ? 'active' : '' }}">
                                                            <div class="mini-card-header">
                                                                <span>Match {{ $loop->iteration }}</span>
                                                                <span class="round-badge">Round {{ $match->round }}</span>
                                                                <span class="status-dot {{ $mStatusClass }}"></span>
                                                            </div>
                                                            <div class="mini-card-body">
                                                                <div class="mini-team {{ $match->winner_id == $match->team1_id ? 'winner' : '' }}">
                                                                    <span>{{ $match->team1 ? $match->team1->name : ($match->team1_placeholder ?? 'BYE') }}</span>
                                                                    <span class="score">{{ $match->team1_score ?? '-' }}</span>
                                                                </div>
                                                                <div class="mini-team {{ $match->winner_id == $match->team2_id ? 'winner' : '' }}">
                                                                    <span>{{ $match->team2 ? $match->team2->name : ($match->team2_placeholder ?? 'BYE') }}</span>
                                                                    <span class="score">{{ $match->team2_score ?? '-' }}</span>
                                                                </div>
                                                            </div>
                                                            @if($match->winner_id)
                                                                <div class="mini-card-footer">
                                                                    <i class="fas fa-trophy"></i> {{ $match->winner->name }} won
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                        </div>
                    @endif
                @endisset

                {{-- ALL SPORTS SECTION --}}
                @if(isset($gameCategories) && $showall == true)
                    @foreach($gameCategories as $category)
                        @php
                            // Get schedules with valid matches, ordered by most recent
                            $schedulesToShow = $category->gameSchedule
                                ->filter(function($schedule) {
                                    return $schedule->gameMatch
                                        ->whereNotNull('match_date')
                                        ->whereNotNull('match_time')
                                        ->isNotEmpty();
                                })
                                ->sortByDesc(function($schedule) {
                                    return optional($schedule->gameMatch
                                        ->whereNotNull('match_date')
                                        ->whereNotNull('match_time')
                                        ->sortByDesc('match_date')
                                        ->first())->match_date;
                                })
                                ->take(3); // Show 3 most recent schedules
                        @endphp

                        @if($schedulesToShow->isNotEmpty())
                            {{-- Category header --}}
                            <div class="tournament-header mb-4 ms-2">
                                <div class="d-flex align-items-center justify-content-start">
                                    <span class="me-2 cat-icons">{!! $category->icon !!}</span>
                                    <div>
                                        <h2 class="tournament-title mb-0">{{ $category->name }}</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap">
                                @foreach($schedulesToShow as $schedule)
                                    @php
                                        // Filter out matches without date/time and sort by date
                                        $matches = $schedule->gameMatch
                                            ->filter(function($match) {
                                                return !is_null($match->match_date) && !is_null($match->match_time);
                                            })
                                            ->sortBy('match_date');
                                        
                                        // Find the most relevant match to display
                                        $now = now();
                                        $upcomingMatch = null;
                                        $latestCompletedMatch = null;
                                        $currentMatch = null;

                                        foreach ($matches as $match) {
                                            $matchTime = \Carbon\Carbon::parse($match->match_date . ' ' . $match->match_time);
                                            
                                            if ($match->winner_id) {
                                                $latestCompletedMatch = $match;
                                            } elseif ($matchTime->gte($now)) {
                                                $upcomingMatch = $upcomingMatch ?? $match;
                                            }
                                        }

                                        // Priority: 1) Latest completed match, 2) Next upcoming match, 3) Most recent past match
                                        $currentMatch = $latestCompletedMatch ?? $upcomingMatch ?? $matches->last();
                                    @endphp

                                    @if($currentMatch)
                                        @php
                                            $matchTime = \Carbon\Carbon::parse($currentMatch->match_date . ' ' . $currentMatch->match_time);
                                            $matchEndTime = $matchTime->copy()->addHours(2); // Assuming 2 hour match duration
                                            
                                            $isCompleted = (bool)$currentMatch->winner_id;
                                            $isLive = !$isCompleted && $now->between($matchTime, $matchEndTime);
                                            $isPast = !$isCompleted && $now->gt($matchEndTime);
                                            
                                            $statusClass = $isCompleted ? 'completed' : ($isLive ? 'live' : ($isPast ? 'Not-Started' : 'scheduled'));
                                            $statusText = $isCompleted ? 'Completed' : ($isLive ? 'Live' : ($isPast ? 'Not-Started' : 'Scheduled'));
                                        @endphp

                                        <div class="col-lg-4 mb-4 p-2">
                                            <div class="match-card">
                                                <div class="match-header">
                                                    <h4 class="match-title">{{ $schedule->name }}</h4>
                                                    <div class="match-status-badge {{ $statusClass }}">
                                                        {{ $statusText }}
                                                    </div>
                                                </div>
                                                
                                                <div class="match-body">
                                                    <!-- Team A -->
                                                    <div class="team team-a {{ $currentMatch->winner_id == $currentMatch->team1_id ? 'winner' : '' }}">
                                                        <div class="team-logo">
                                                            @if($currentMatch->team1)
                                                                <img src="{{ asset($currentMatch->team1->avatar ?? 'assets/upload/team/default-img.webp') }}" 
                                                                    alt="{{ $currentMatch->team1->name }}">
                                                            @else
                                                                <div class="team-initials">BYE</div>
                                                            @endif
                                                        </div>
                                                        <div class="team-info">
                                                            <h5 class="team-name">
                                                                {{ $currentMatch->team1 ? $currentMatch->team1->name : ($currentMatch->team1_placeholder ?? 'BYE') }}
                                                            </h5>
                                                            @if($currentMatch->team1_score !== null)
                                                                <div class="team-score">{{ $currentMatch->team1_score }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Match Info -->
                                                    <div class="match-info">
                                                        <div class="match-round">Round {{ $currentMatch->round }}</div>
                                                        <div class="vs-circle">VS</div>
                                                        <div class="match-time">
                                                            {{ $matchTime->format('M d, H:i') }}
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Team B -->
                                                    <div class="team team-b {{ $currentMatch->winner_id == $currentMatch->team2_id ? 'winner' : '' }}">
                                                        <div class="team-info">
                                                            <h5 class="team-name">
                                                                {{ $currentMatch->team2 ? $currentMatch->team2->name : ($currentMatch->team2_placeholder ?? 'BYE') }}
                                                            </h5>
                                                            @if($currentMatch->team2_score !== null)
                                                                <div class="team-score">{{ $currentMatch->team2_score }}</div>
                                                            @endif
                                                        </div>
                                                        <div class="team-logo">
                                                            @if($currentMatch->team2)
                                                                <img src="{{ asset($currentMatch->team2->avatar ?? 'assets/upload/team/default-img.webp') }}" 
                                                                    alt="{{ $currentMatch->team2->name }}">
                                                            @else
                                                                <div class="team-initials">BYE</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                @if($currentMatch->winner_id)
                                                    <div class="match-footer">
                                                        <div class="winner-announcement">
                                                            <i class="fas fa-trophy"></i>
                                                            {{ $currentMatch->winner->name }} won by 
                                                            @if(is_numeric($currentMatch->team1_score) && is_numeric($currentMatch->team2_score))
                                                                {{ abs($currentMatch->team1_score - $currentMatch->team2_score) }} points
                                                            @else
                                                                default
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($matches->count() > 1)
                                                    <button class="btn btn-outline-primary view-more-matches" 
                                                            data-schedule="{{ $schedule->id }}">
                                                        View All Matches <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                    
                                                    <div class="all-matches-container" id="all-matches-{{ $schedule->id }}" style="display:none;">
                                                        @foreach($matches as $match)
                                                            @php
                                                                $mTime = \Carbon\Carbon::parse($match->match_date . ' ' . $match->match_time);
                                                                $mEndTime = $mTime->copy()->addHours(2);
                                                                $mIsCompleted = (bool)$match->winner_id;
                                                                $mIsLive = !$mIsCompleted && $now->between($mTime, $mEndTime);
                                                                $mIsPast = !$mIsCompleted && $now->gt($mEndTime);
                                                                
                                                                $mStatusClass = $mIsCompleted ? 'completed' : ($mIsLive ? 'live' : ($mIsPast ? 'Not-Started' : 'scheduled'));
                                                            @endphp
                                                            
                                                            <div class="match-mini-card {{ $match->id == $currentMatch->id ? 'active' : '' }}">
                                                                <div class="mini-card-header">
                                                                    <span>Match {{ $loop->iteration }}</span>
                                                                    <span class="round-badge">Round {{ $match->round }}</span>
                                                                    <span class="status-dot {{ $mStatusClass }}"></span>
                                                                </div>
                                                                <div class="mini-card-body">
                                                                    <div class="mini-team {{ $match->winner_id == $match->team1_id ? 'winner' : '' }}">
                                                                        <span>{{ $match->team1 ? $match->team1->name : ($match->team1_placeholder ?? 'BYE') }}</span>
                                                                        <span class="score">{{ $match->team1_score ?? '-' }}</span>
                                                                    </div>
                                                                    <div class="mini-team {{ $match->winner_id == $match->team2_id ? 'winner' : '' }}">
                                                                        <span>{{ $match->team2 ? $match->team2->name : ($match->team2_placeholder ?? 'BYE') }}</span>
                                                                        <span class="score">{{ $match->team2_score ?? '-' }}</span>
                                                                    </div>
                                                                </div>
                                                                @if($match->winner_id)
                                                                    <div class="mini-card-footer">
                                                                        <i class="fas fa-trophy"></i> {{ $match->winner->name }} won
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL START -->
    <div class="modal fade" id="allMatchesModal" tabindex="-1" aria-labelledby="allMatchesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="allMatchesModalLabel">All Matches</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalMatchesContent">
                    <!-- Content will be loaded here via AJAX -->
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL END -->
@endsection

@push('style')
<style>    
    .trophy-img {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }
    
    .tournament-title {
        font-size: 2rem;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        font-weight: 700;
        background: linear-gradient(to right, #fafafa, #17ff80, #06ca5f);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .cat-icons i{
        font-size: 22px;
        line-height: 20px;
        border: 1px dashed #7e7e7e;
        padding: 10px;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #ffffff;
    }
    
    .tournament-subtitle {
        font-size: 1rem;
        opacity: 0.9;
    }
    
    /* Match Card Styles */
    .match-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height:100%;
    }
    
    .match-card:hover {
        transform: translateY(-5px);
    }
    
    .match-header {
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        padding: 1rem 1.2rem 1.2rem;
        background:var(--primary);
        color: white;
    }
    
    .match-title {
        text-align:center;
        margin: 0 0px 5px;
        font-weight: 600;
        font-size: 18px;
        color: #233645 !important;
    }
    
    .match-status-badge {
        padding: 0.2rem 0.8rem;
        border-radius: 20px;
        font-size: 16px;
        font-weight: 600;
        position:absolute;
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .match-status-badge.live {
        background: #e74c3c;
        animation: pulse 1.5s infinite;
    }
    
    .match-status-badge.scheduled {
        background:rgb(65, 55, 255);
    }    
    
    .match-status-badge.completed {
        background: #000;
    }   
    
    .match-status-badge.past {
        background: #233645;
    }
    .match-status-badge.Not-Started{
        background: #233645;
    }
    
    .match-body {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1.5rem 10px;
    }
    
    .team {
        flex: 1;
        display: flex;
        align-items: center;
    }
    
    .team-a {
        justify-content: flex-start;
    }
    
    .team-b {
        justify-content: flex-end;
    }
    
    .team.winner {
        position: relative;
    }
    
    .team.winner::after {
        content: 'Winner';
        position: absolute;
        top: 0px;
        background: #f1c40f;
        color: #2c3e50;
        padding: 0.2rem 0.8rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
    }
    
    .team-a.winner::after {
        left: 17px;
    }
    
    .team-b.winner::after {
        right: 20px;
    }
    
    .team-logo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 1rem;
        overflow: hidden;
        border: 2px solid #e9ecef;
    }
    
    .team-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .team-initials {
        font-weight: 700;
        font-size: 1.2rem;
        color: #6c757d;
    }
    
    .team-info {
        text-align: center;
    }
    
    .team-name {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
        color:#000 !important;
    }
    
    .team-score {
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 0.5rem;
        color:#000 !important;
    }
    
    .match-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0 1.5rem;
    }
    
    .match-round {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    
    .vs-circle {
        width: 40px;
        height: 40px;
        background: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .match-time {
        font-size: 0.6rem;
        margin-top: 0.5rem;
        color: #6c757d;
    }
    
    .match-footer {
        padding: 0.8rem;
        background: #f8f9fa;
        text-align: center;
        border-top: 1px solid #e9ecef;
    }
    
    .winner-announcement {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .winner-announcement i {
        color: #f1c40f;
        margin-right: 0.5rem;
    }
    
    .view-more-matches {
        width: 100%;
        padding: 0.8rem;
        background: #f8f9fa;
        border: none;
        border-top: 1px solid #e9ecef;
        color: #3498db;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .view-more-matches:hover {
        background: #e9ecef;
    }
    
    .view-more-matches i {
        transition: transform 0.3s ease;
    }
    
    .view-more-matches.collapsed i {
        transform: rotate(180deg);
    }
    
    .all-matches-container {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease;
    }
    
    .all-matches-container.show {
        max-height: 1000px; /* Adjust based on content */
    }
    
    .match-mini-card {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .match-mini-card:last-child {
        border-bottom: none;
    }
    
    .match-mini-card:hover {
        background: #f8f9fa;
    }
    
    .match-mini-card.active {
        background: #e3f2fd;
    }
    
    .mini-card-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    
    .round-badge {
        background: #e9ecef;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        font-size: 0.7rem;
    }
    
    .mini-card-body {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .mini-team {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.3rem 0;
    }
    
    .mini-team.winner {
        font-weight: 600;
        color: #27ae60;
    }
    
    .mini-team .score {
        font-weight: 600;
    }
    
    .mini-card-footer {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .mini-card-footer i {
        color: #f1c40f;
        margin-right: 0.3rem;
        font-size: 0.7rem;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .match-body {
            flex-direction: column;
            padding: 1rem;
        }
        
        .team {
            width: 100%;
            justify-content: center !important;
            margin-bottom: 1rem;
        }
        
        .team-b {
            flex-direction: row-reverse;
        }
        
        .match-info {
            margin: 1rem 0;
            width: 100%;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
        
        .vs-circle {
            margin: 0 1rem;
        }
        
        .team.winner::after {
            top: -15px;
        }
    }

    /* Modal Styles */
#allMatchesModal .modal-content {
    border-radius: 10px;
    border: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

#allMatchesModal .modal-header {
    background: #8fb568;
    color: white;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
}

#allMatchesModal .modal-title {
    font-weight: 600;
}

#allMatchesModal .modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

#allMatchesModal .table {
    margin-bottom: 0;
}

#allMatchesModal .table th {
    font-weight: 600;
    background: #000;
    position: sticky;
    top: 0;
}

#allMatchesModal .table tr:hover {
    background-color: rgba(0,0,0,0.02);
}

#allMatchesModal .modal-footer {
    border-top: none;
    background: #f8f9fa;
    border-radius: 0 0 10px 10px;
}

#allMatchesModal .modal-header {
    background: #8fb568;
    color: white;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
    padding: 1.25rem 1.5rem;
}

#allMatchesModal .modal-title {
    font-weight: 700;
    font-size: 1.25rem;
    margin: 0;
}

#allMatchesModal .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

#allMatchesModal .btn-close:hover {
    opacity: 1;
}
</style>
@endpush

@push('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Toggle all matches visibility
        document.querySelectorAll('.view-more-matches').forEach(button => {
            button.addEventListener('click', function() {
                const scheduleId = this.getAttribute('data-schedule');
                const container = document.getElementById(`all-matches-${scheduleId}`);
                
                if (container.style.display === 'none') {
                    container.style.display = 'block';
                    this.classList.add('collapsed');
                    this.innerHTML = 'Hide Matches <i class="fas fa-chevron-up"></i>';
                } else {
                    container.style.display = 'none';
                    this.classList.remove('collapsed');
                    this.innerHTML = 'View All Matches <i class="fas fa-chevron-down"></i>';
                }
            });
        });
        
        // Add animation to live matches
        setInterval(() => {
            document.querySelectorAll('.match-status-badge.live').forEach(badge => {
                badge.style.opacity = badge.style.opacity === '0.6' ? '1' : '0.6';
            });
        }, 1000);
    });
</script>
<script>
    function toggleMatches(button, scheduleId) {
        const container = document.getElementById(`all-matches-${scheduleId}`);
        const icon = button.querySelector('i');
        
        if (container.style.display === 'none') {
            container.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            button.innerHTML = 'Hide Matches <i class="fas fa-chevron-up"></i>';
        } else {
            container.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            button.innerHTML = 'View All Matches <i class="fas fa-chevron-down"></i>';
        }
    }
</script>
<!-- Keep your existing script section -->
 {{-- JavaScript for Toggling Full Table Visibility --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".view-more-btn").forEach(button => {
            button.addEventListener("click", function () {
                let scheduleId = this.getAttribute("data-schedule");
                let table = document.querySelector("#table-" + scheduleId);
                let expanded = this.getAttribute("data-expanded") === "true";

                if (!expanded) {
                    this.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Loading...`; // Show loader
                    this.disabled = true;

                    fetch(`/get-full-schedule/${scheduleId}`)
                        .then(response => response.json())
                        .then(data => {
                            let newTableContent = `
                                <thead>
                                    <tr>
                                        <th>Match No.</th>
                                        <th>Round</th>
                                        <th>Team A</th>
                                        <th>Score A</th>
                                        <th>Team B</th>
                                        <th>Score B</th>
                                        <th>Winner</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                            `;

                            data.forEach((match, index) => {
                                let winner = "TBD";
                                if (!isNaN(match.team1_score) && !isNaN(match.team2_score)) {
                                    if (match.team1_score > match.team2_score) {
                                        winner = match.team1_name;
                                    } else if (match.team2_score > match.team1_score) {
                                        winner = match.team2_name;
                                    } else {
                                        winner = "Draw";
                                    }
                                }

                                let status = match.winner_id ? "Completed" : "Pending";

                                newTableContent += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${match.round}</td>
                                        <td>${match.team1_name ?? 'BYE'}</td>
                                        <td>${match.team1_score ?? '-'}</td>
                                        <td>${match.team2_name ?? 'BYE'}</td>
                                        <td>${match.team2_score ?? '-'}</td>
                                        <td>${winner}</td>
                                        <td>${status}</td>
                                    </tr>
                                `;
                            });

                            newTableContent += "</tbody>";

                            table.innerHTML = newTableContent;
                            this.innerHTML = "View Less";
                            this.setAttribute("data-expanded", "true");
                        })
                        .catch(error => {
                            console.error("Error loading data:", error);
                            this.innerHTML = "View More";
                        })
                        .finally(() => {
                            this.disabled = false;
                        });
                } else {
                    let firstRowContent = table.querySelector("tbody").innerHTML.split("</tr>")[0] + "</tr>";
                    table.innerHTML = `<thead>${table.querySelector("thead").innerHTML}</thead><tbody>${firstRowContent}</tbody>`;
                    this.innerHTML = "View More";
                    this.setAttribute("data-expanded", "false");
                }
            });
        });
    });
</script>

    @php
        $segments = request()->segments();
        $last  = end($segments);

    @endphp

    <script>
        let getMatchList = new Vue({
            el: "#getMatchList",
            data: {

                loaded: true,
                currency_symbol: "{{basicControl()->currency_symbol}}",
                currency: "{{basicControl()->base_currency}}",
                minimum_bet: "{{basicControl()->minimum_bet}}",
                allSports_filter: [],
                upcoming_filter: [],

                selectedData: {},
                betSlip: [],
                totalOdds: 0,
                minimumAmo: 1,
                return_amount: 0,
                win_charge: "{{basicControl()->win_charge}}",
                form: {
                    amount: ''
                },
                showType: 'live'
            },
            mounted() {
                var showType = localStorage.getItem('showType');
                if (showType == null) {
                    localStorage.setItem("showType", 'live');
                }
                this.showType = localStorage.getItem("showType")

                this.getMatches();
                this.getSlip();
                this.getEvents();


            },
            methods: {
                async getMatches() {
                    var _this = this;
                    var _segment = "{{Request::segment(1)}}"
                    var routeName = "{{Request::route()->getName()}}"
                    var $lastSegment = "{{$last}}"

                    var $url = '{{route('allSports')}}';

                    if (routeName == 'category') {
                        $url = '{{route('allSports')}}?categoryId=' + $lastSegment;
                    }
                    if (routeName == 'tournament') {
                        $url = '{{route('allSports')}}?tournamentId=' + $lastSegment;
                    }

                    if (routeName == 'match') {
                        $url = '{{route('allSports')}}?matchId=' + $lastSegment;
                    }


                    await axios.get($url)
                        .then(function (response) {
                            _this.allSports_filter = response.data.liveList;
                            _this.upcoming_filter = response.data.upcomingList;
                        })
                        .catch(function (error) {
                            console.log(error);
                        })
                },

                addToSlip(data) {
                    if (data.is_unlock_question == 1 || data.is_unlock_match == 1) {
                        return 0;
                    }
                    var _this = this;
                    const index = _this.betSlip.findIndex(object => object.match_id === data.match_id);
                    if (index === -1) {
                        _this.betSlip.push(data);
                        Notiflix.Notify.success("Added to Bet slip");
                    } else {
                        var result = _this.betSlip.map(function (obj) {
                            if (obj.match_id == data.match_id) {
                                obj = data
                            }
                            return obj
                        });
                        _this.betSlip = result

                        Notiflix.Notify.info("Bet slip has been updated");
                    }
                    _this.totalOdds = _this.oddsCalc(_this.betSlip)
                    localStorage.setItem("newBetSlip", JSON.stringify(_this.betSlip));
                },
                getSlip() {
                    var _this = this;
                    var selectData = JSON.parse(localStorage.getItem('newBetSlip'));
                    if (selectData != null) {
                        _this.betSlip = selectData;
                    } else {
                        _this.betSlip = []
                    }
                    _this.totalOdds = _this.oddsCalc(_this.betSlip)
                },

                removeItem(obj) {
                    var _this = this;
                    _this.betSlip.splice(_this.betSlip.indexOf(obj), 1);
                    _this.totalOdds = _this.oddsCalc(_this.betSlip)

                    var selectData = JSON.parse(localStorage.getItem('newBetSlip'));
                    var storeIds = selectData.filter(function (item) {
                        if (item.id === obj.id) {
                            return false;
                        }
                        return true;
                    });
                    localStorage.setItem("newBetSlip", JSON.stringify(storeIds));
                },

                oddsCalc(obj) {
                    var ratio = 1;
                    for (var property in obj) {
                        ratio *= parseFloat(obj[property].ratio);
                    }
                    return ratio.toFixed(3);
                },

                decrement() {
                    if (this.form.amount > this.minimumAmo) {
                        this.form.amount--;
                        this.return_amount = parseFloat(this.form.amount * this.totalOdds).toFixed(3);

                        return 0;
                    }
                    return 1;
                },
                increment() {
                    this.form.amount++;
                    this.return_amount = parseFloat(this.form.amount * this.totalOdds).toFixed(3);
                    return 0;
                },
                calc(val) {
                    if (isNaN(val)) {
                        val = 0
                    }
                    if (0 >= val) {
                        val = 0;
                    }
                    this.return_amount = parseFloat(val * this.totalOdds).toFixed(2);
                },

                goMatch(item) {
                    var $url = '{{ route("match", [":match_name",":match_id"]) }}';
                    $url = $url.replace(':match_name', item.slug);
                    $url = $url.replace(':match_id', item.id);
                    window.location.href = $url;
                },

                getEvents() {
                    let _this = this;
                    // Pusher.logToConsole = true;
                    let pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                        encrypted: true,
                        cluster: "{{ env('PUSHER_APP_CLUSTER') }}"
                    });
                    var channel = pusher.subscribe('match-notification');

                    channel.bind('App\\Events\\MatchNotification', function (data) {
                        console.log(data)
                        if (data && data.type == 'Edit') {
                            _this.updateEventData(data)
                        } else if (data && data.type != 'Edit') {
                            _this.enlistedEventData(data)
                        }
                    });

                },
                updateEventData(data) {
                    var _this = this;
                    var list = _this.allSports_filter;
                    const result = list.map(function (obj) {
                        if (obj.id == data.match.id) {
                            obj = data.match
                        }
                        return obj
                    });
                    _this.allSports_filter = result


                    var list2 = _this.upcoming_filter;


                    const upcomingResult = list2.map(function (obj) {
                        if (obj.id == data.match.id) {
                            obj = data.match
                        }
                        return obj
                    });

                    _this.upcoming_filter = upcomingResult
                },
                enlistedEventData(data) {
                    var _this = this;
                    if (data && data.type == 'Enlisted') {
                        var list = _this.allSports_filter;
                        list.push(data.match);
                    }
                    if (data && data.type == 'UpcomingList') {
                        var upcomingList = _this.upcoming_filter;
                        upcomingList.push(data.match);
                    }
                },
                betPlace() {
                    var _this = this;
                    var authCheck = "{{auth()->check()}}"
                    if (authCheck !== '1') {
                        window.location.href = "{{route('login')}}"
                        return 0;
                    }
                    if (_this.betSlip.length == 0) {
                        Notiflix.Notify.failure("Please make a bet slip");
                        return 0
                    }
                    if (_this.form.amount == '') {
                        Notiflix.Notify.failure("Please put a amount");
                        return 0
                    }
                    if (0 > (_this.form.amount)) {
                        Notiflix.Notify.failure("Please put a valid amount");
                        return 0
                    }
                    if (parseInt(_this.minimum_bet) > parseInt(_this.form.amount)) {
                        Notiflix.Notify.failure("Minimum Bet " + _this.minimum_bet + " " + _this.currency);
                        return 0
                    }
                    axios.post('{{route('user.betSlip')}}', {
                        amount: _this.form.amount,
                        activeSlip: _this.betSlip,
                    })
                        .then(function (response) {
                            if (response.data.errors) {
                                for (err in response.data.errors) {
                                    let error = response.data.errors[err][0]
                                    Notiflix.Notify.failure("" + error);
                                }
                                return 0;
                            }
                            if (response.data.newSlipMessage) {
                                Notiflix.Notify.warning("" + response.data.newSlipMessage);
                                var newSlip = response.data.newSlip;
                                var unlisted = _this.getDifference(_this.betSlip, newSlip);
                                const newUnlisted = unlisted.map(function (obj) {
                                    obj.is_unlock_match = 1;
                                    obj.is_unlock_question = 1;
                                    return obj
                                });
                                _this.betSlip.concat(newSlip, newUnlisted);
                                localStorage.setItem("newBetSlip", JSON.stringify(_this.betSlip));
                                return 0;
                            }

                            if (response.data.success) {
                                _this.betSlip = [];
                                localStorage.setItem("newBetSlip", JSON.stringify(_this.betSlip));
                                Notiflix.Notify.success("Your bet has place successfully");

                                return 0;
                            }

                        })
                        .catch(function (err) {

                        });
                },

                getDifference(array1, array2) {
                    return array1.filter(object1 => {
                        return !array2.some(object2 => {
                            return object1.id === object2.id;
                        });
                    });
                },
                slicedArray(items) {
                    return  Object.values(items)[0];
                },
                liveUpComing(type){
                    localStorage.setItem("showType", type);
                    this.showType = type
                }


            }
        });

    </script>
   <script>
    document.addEventListener("DOMContentLoaded", function() {
        // View All Matches button click handler
        document.querySelectorAll('.view-more-matches').forEach(button => {
            button.addEventListener('click', function() {
                const scheduleId = this.getAttribute('data-schedule');
                const tournamentName = this.closest('.match-card').querySelector('.match-title').textContent;
                
                // Show loading state
                document.getElementById('modalMatchesContent').innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading matches...</p>
                    </div>
                `;
                
                // Update modal title with tournament name
                document.getElementById('allMatchesModalLabel').textContent = tournamentName;
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('allMatchesModal'));
                modal.show();
                
                // Fetch the matches data
                fetch(`/get-schedule-matches/${scheduleId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Format the matches data
                        let matchesHtml = `
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Match</th>
                                            <th>Round</th>
                                            <th>Team A</th>
                                            <th>Score</th>
                                            <th>Team B</th>
                                            <th>Score</th>
                                            <th>Status</th>
                                            <th>Date/Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.matches.forEach((match, index) => {
                            // Initialize variables
                            let status = 'Scheduled';
                            let statusClass = 'text-primary';
                            let formattedDate = '--';
                            
                            // Try to parse date if available
                            if (match.match_date && match.match_time) {
                                try {
                                    const matchTime = new Date(`${match.match_date}T${match.match_time}`);
                                    if (!isNaN(matchTime.getTime())) {
                                        // Format date as "May 19, 2025 11:00 AM"
                                        formattedDate = matchTime.toLocaleString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                            year: 'numeric',
                                            hour: 'numeric',
                                            minute: '2-digit',
                                            hour12: true
                                        });
                                        
                                        const now = new Date();
                                        const endTime = new Date(matchTime.getTime() + 2 * 60 * 60 * 1000); // 2 hours duration
                                        
                                        if (match.winner_id) {
                                            status = 'Completed';
                                            statusClass = 'text-success';
                                        } else if (now >= matchTime && now <= endTime) {
                                            status = 'Live';
                                            statusClass = 'text-danger';
                                        } else if (now > endTime) {
                                            status = 'Not-Started';
                                            statusClass = 'text-muted';
                                        }
                                    }
                                } catch (e) {
                                    console.error('Error parsing date:', e);
                                }
                            }
                            
                            matchesHtml += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${match.round || '--'}</td>
                                    <td>${match.team1 ? match.team1.name : (match.team1_placeholder || 'BYE')}</td>
                                    <td>${match.team1_score !== null ? match.team1_score : '-'}</td>
                                    <td>${match.team2 ? match.team2.name : (match.team2_placeholder || 'BYE')}</td>
                                    <td>${match.team2_score !== null ? match.team2_score : '-'}</td>
                                    <td class="${statusClass}">${status}</td>
                                    <td>${formattedDate}</td>
                                </tr>
                            `;
                        });
                        
                        matchesHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                        
                        document.getElementById('modalMatchesContent').innerHTML = matchesHtml;
                    })
                    .catch(error => {
                        console.error('Error loading matches:', error);
                        document.getElementById('modalMatchesContent').innerHTML = `
                            <div class="alert alert-danger">
                                Failed to load matches. Please try again.
                            </div>
                        `;
                    });
            });
        });
    });
    </script>
@endpush