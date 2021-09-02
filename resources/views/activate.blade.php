<html>
    <div>
        <h1>Welcome {{ $email }}</h1>
        <form action={{ $url }}>
            <input type="submit" value="Confirm registration" />
        </form>
    </div>
</html>