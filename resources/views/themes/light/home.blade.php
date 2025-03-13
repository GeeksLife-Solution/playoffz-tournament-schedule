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
            <div class="top p-1 d-flex">
                <button @click="liveUpComing('live')" type="button" :class="{light: (showType == 'upcoming')}"  class="btn-custom me-1">
                    <i class="las la-podcast"></i>
                    @lang('Live')
                </button>
                <button @click="liveUpComing('upcoming')" type="button" :class="{light: (showType == 'live')}"  class="btn-custom ">
                    <i class="las la-meteor"></i>
                    @lang('Upcoming')
                </button>
            </div>
            @include($theme.'partials.home.leftMenu')

            <div class="bottom p-1">
                <a href="{{route('betResult')}}" class="btn-custom light w-100">@lang('results')</a>
            </div>
        </div>

        @include($theme.'partials.home.rightbar')

        <!-- contents -->
        <div class="content">
            @include($theme.'partials.home.slider')
            @include($theme.'partials.home.navbar')
            @if(Request::routeIs('match'))
                @include($theme.'partials.home.match')
            @else
                @include($theme.'partials.home.content')
            @endif

           {{-- EACH CATGEORY WISE - SCOREBOARD FOR A PARTICULAR CATEGORY --}}
            @isset($selectedCategory)
                @if($selectedCategory)
                    <h3 class="text-center mb-2 mt-2">{{ $selectedCategory->name }} - Score List</h3>
                    @foreach($selectedCategory->gameSchedule as $schedule)
                        <div class="schedule-section mb-4">
                            <h4 class="schedule-title bg-dark text-white p-2">
                                {{ $schedule->name }}
                            </h4>

                            @php
                                $matches = $schedule->gameMatch;
                                $finalMatch = $matches->whereNotNull('winner_id')->last(); // Get the last match with a winner
                                $initialMatch = $finalMatch ?? $matches->first(); // Show final match if available, else first match
                            @endphp

                            <table class="table table-dark table-bordered text-center match-table mb-0" id="table-{{ $schedule->id }}">
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
                                    @if ($initialMatch)
                                        @php
                                            $teamA = $initialMatch->team1 ? $initialMatch->team1->name : ($initialMatch->team1_placeholder ?? 'BYE');
                                            $teamB = $initialMatch->team2 ? $initialMatch->team2->name : ($initialMatch->team2_placeholder ?? 'BYE');
                                            $scoreA = $initialMatch->team1_score ?? '-';
                                            $scoreB = $initialMatch->team2_score ?? '-';

                                            if (is_numeric($scoreA) && is_numeric($scoreB)) {
                                                if ($scoreA > $scoreB) {
                                                    $winner = $teamA;
                                                } elseif ($scoreB > $scoreA) {
                                                    $winner = $teamB;
                                                } else {
                                                    $winner = 'Draw';
                                                }
                                            } else {
                                                $winner = 'TBD';
                                            }

                                            $status = (!is_null($initialMatch->winner_id) && is_numeric($scoreA) && is_numeric($scoreB)) ? 'Completed' : 'Pending';
                                        @endphp

                                        <tr>
                                            <td>{{ $loop->index+1 }}</td>
                                            <td>{{ $initialMatch->round }}</td>
                                            <td>{{ $teamA }}</td>
                                            <td>{{ $scoreA }}</td>
                                            <td>{{ $teamB }}</td>
                                            <td>{{ $scoreB }}</td>
                                            <td>{!! $winner !!}</td>
                                            <td>{{ $status }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            {{-- View More Button --}}
                            @if ($matches->count() > 1)
                                <button class="btn btn-primary view-more-btn w-100 rounded-0" data-schedule="{{ $schedule->id }}" data-expanded="false">View More</button>
                            @endif
                        </div>
                    @endforeach
                @endif
            @endisset
            {{-- END SCORE BOARD --}}

            {{-- ALL SPORTS WISE --}}
            @if(isset($gameCategories) && $showall == true)
                @foreach($gameCategories as $category)
                    @php
                        // Filter schedules that have at least one match
                        $validSchedules = $category->gameSchedule->filter(function($schedule) {
                            return $schedule->gameMatch->isNotEmpty();
                        });
                    @endphp

                    @if ($validSchedules->isNotEmpty()) 
                        <div class="category-section mb-5">
                            <h3 class="text-center bg-success text-white p-3">{{ $category->name }} - Score List</h3>

                            @foreach($validSchedules as $schedule)
                                <div class="schedule-section mb-4">
                                    <h4 class="schedule-title bg-dark text-white p-2">
                                        {{ $schedule->name }}
                                    </h4>

                                    @php
                                        $matches = $schedule->gameMatch;
                                        $firstMatch = $matches->first(); // Get first match
                                    @endphp

                                    <table class="table table-dark table-bordered text-center match-table mb-0" id="table-{{ $schedule->id }}">
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
                                            @if ($firstMatch)
                                                @php
                                                    $teamA = $firstMatch->team1 ? $firstMatch->team1->name : ($firstMatch->team1_placeholder ?? 'BYE');
                                                    $teamB = $firstMatch->team2 ? $firstMatch->team2->name : ($firstMatch->team2_placeholder ?? 'BYE');
                                                    $scoreA = $firstMatch->team1_score ?? '-';
                                                    $scoreB = $firstMatch->team2_score ?? '-';

                                                    if (is_numeric($scoreA) && is_numeric($scoreB)) {
                                                        $winner = $scoreA > $scoreB ? $teamA : ($scoreB > $scoreA ? $teamB : 'Draw');
                                                    } else {
                                                        $winner = 'TBD';
                                                    }

                                                    $status = (!is_null($firstMatch->winner_id) && is_numeric($scoreA) && is_numeric($scoreB)) ? 'Completed' : 'Pending';
                                                @endphp

                                                <tr>
                                                    <td>{{ $firstMatch->id }}</td>
                                                    <td>{{ $firstMatch->round }}</td>
                                                    <td>{{ $teamA }}</td>
                                                    <td>{{ $scoreA }}</td>
                                                    <td>{{ $teamB }}</td>
                                                    <td>{{ $scoreB }}</td>
                                                    <td>{!! $winner !!}</td>
                                                    <td>{{ $status }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>

                                    {{-- View More Button --}}
                                    @if ($matches->count() > 1)
                                        <button class="btn btn-primary view-more-btn w-100 rounded-0" data-schedule="{{ $schedule->id }}" data-expanded="false">View More</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @endif
            {{-- ALL SPORTS WISE END --}}

        </div>

    </div>
@endsection

@push('script')
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
@endpush
