    <h3>Convert your Citizens1 npc-profiles.yml to Citizens2 standards</h3>
        <form action="#" method="post" enctype="multipart/form-data">
            {if $error}<div class="alert alert-error">{$error}</div>{/if}
            <input type="radio" name="traderMode" value="none" checked/> Do not convert trader data<br />
            <input type="radio" name="traderMode" value="CitiTraders" /> Convert Trader data to CitiTraders<br />
            <input type="radio" name="traderMode" value="DtlTraders" /> Convert Trader data to DtlTraders<br /><br />
            <label for="file">Filename:</label>
            <input type="file" name="file" id="file" /><br>
            <input type="submit" name="submit" value="Submit" />
        </form>