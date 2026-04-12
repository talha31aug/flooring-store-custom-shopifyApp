<p>You are: {{ $shopDomain ?? Auth::user()->name }}</p>
<h2>Store Details</h2>
<div class="store-details">
    <p>Update Status: {{ $updatedTheme ?? 'No updates' }}</p>
    <p>Error: {{ $error ?? 'No errors' }}</p>
</div>
