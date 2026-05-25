@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ rtrim(config('app.url'), '/') }}/logo/wibook.png" class="logo" alt="{{ config('app.name', 'Wibook Financing') }} Logo" style="max-width: 180px; height: auto; display: block; margin: 0 auto;">
</a>
</td>
</tr>
