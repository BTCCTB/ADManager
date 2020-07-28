<form method="post" action="{{ path('<?=$route_name;?>_delete', {'<?=$entity_identifier;?>': identifier}) }}" onsubmit="return confirm('Are you sure you want to delete this item?');">
    <input type="hidden" name="_method" value="DELETE">
    <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ identifier) }}">
    <button class="btn btn-outline-danger" type="submit">
        <i class='fas fa-trash' aria-hidden="true"></i> {{ 'Delete' | trans }}
    </button>
</form>