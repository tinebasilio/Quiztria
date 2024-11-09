<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Bee Score Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            page-break-inside: auto;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            word-wrap: break-word;
            white-space: normal;
            font-size: 9px;
        }
        th {
            background-color: #FFD700;
            font-weight: bold;
            font-size: 9px;
        }
        .easy {
            background-color: #FFCCCB;
            font-size: 8px;
        }
        .average {
            background-color: #FFEB99;
            font-size: 8px;
        }
        .difficult {
            background-color: #ADD8E6;
            font-size: 8px;
        }
        .clincher {
            background-color: #98FB98;
            font-size: 8px;
        }
        .total {
            background-color: #F0E68C;
            font-weight: bold;
        }
        .footer {
            background-color: #D3D3D3;
        }
        .correct {
            color: green;
            font-weight: bold;
        }
        .incorrect {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Quiz Bee Results</h1>
<h2>Room: {{ $room->room_name ?? 'N/A' }}</h2>

<table>
    <thead>
        <tr>
            <th rowspan="2">NAME OF PARTICIPANT</th>
            <!-- Dynamically generate headers based on question count and points per difficulty -->
            @foreach($data['questionsCount'] as $difficulty => $info)
                <th colspan="{{ $info['count'] + 1 }}" class="{{ strtolower($difficulty) }}">
                    {{ strtoupper($difficulty) }} ({{ $info['points'] }} POINT{{ $info['points'] > 1 ? 'S' : '' }})
                </th>
            @endforeach
            <th rowspan="2" class="total">Total Scores:</th>
            <th rowspan="2" class="total">Rankings</th>
        </tr>
        <tr>
            <!-- Generate question columns and a total column for each difficulty -->
            @foreach($data['questionsCount'] as $difficulty => $info)
                @for ($i = 1; $i <= $info['count']; $i++)
                    <th>Q{{ $i }}</th>
                @endfor
                <th>Total {{ $difficulty }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($data['participants'] as $participant)
        <tr>
            <td>{{ $participant['name'] }}</td>
            @php
                $overallMaxScore = 0;
                $overallScore = 0;
            @endphp
            @foreach($data['questionsCount'] as $difficulty => $info)
                @php
                    $maxScore = $info['count'] * $info['points']; // Max possible score for this difficulty
                    $actualScore = array_sum(array_column($participant['answers'][$difficulty] ?? [], 'points'));
                    $overallMaxScore += $maxScore;
                    $overallScore += $actualScore;
                @endphp
                @for ($i = 1; $i <= $info['count']; $i++)
                    <td>
                        @if(isset($participant['answers'][$difficulty][$i - 1]))
                            <span class="{{ $participant['answers'][$difficulty][$i - 1]['isCorrect'] ? 'correct' : 'incorrect' }}">
                                {{ $participant['answers'][$difficulty][$i - 1]['points'] }}
                            </span>
                        @else
                            0
                        @endif
                    </td>
                @endfor
                <td>{{ $actualScore }}/{{ $maxScore }}</td> <!-- Display actual/max score for difficulty -->
            @endforeach
            <td>{{ $overallScore }}/{{ $overallMaxScore }}</td> <!-- Display overall actual/max score -->
            <td>{{ $participant['rank'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="signature">
    <p>________________________________</p>
    <p>  Signature over Printed Name</p>
</div>
<div class="signature">
    <p>________________________________</p>
    <p>  Signature over Printed Name</p>
</div>
<div class="signature">
    <br>________________________________</br>
    <br>  Signature over Printed Name</br>
</div>

</body>
</html>
