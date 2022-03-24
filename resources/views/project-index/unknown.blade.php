@foreach($projects as $project)
    <a href="{{ $project->url() }}">{{ $project->name . '.test' }}</a>
    <br/>
@endforeach
