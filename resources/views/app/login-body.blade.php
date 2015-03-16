<div id="loginbox">

    <form id="loginform" class="form-vertical" method="POST">
        <input type="hidden" name="recover" value="0">
        <div class="control-group normal_text logo-container"><h3><img src="/img/logo-cerberus-256x256.png" alt="" /></h3></div>
        {{ $_html }}
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box">
                    <span class="add-on bg_lg"><i class="icon-user"></i></span>
                    <input class="email required" autofocus type="text" id="email_addr_text" name="email_addr_text" placeholder="Email Address" />
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box">
                    <span class="add-on bg_ly"><i class="icon-lock"></i></span>
                    <input class="password required" id="password_text" placeholder="Password"
                            name="password_text" type="password" />
                </div>
            </div>
        </div>
        <div class="form-actions">
            <span class="pull-left"><a href="#" class="flip-link btn btn-info" id="to-recover">Lost password?</a></span>
            <span class="pull-right"><button type="submit" class="btn btn-success"> Login</button></span>
        </div>
    </form>

    <form id="recoverform" class="form-vertical" action="/app/recover" method="POST">
        <input type="hidden" name="recover" value="1">
        <p class="normal_text">Enter your email address below and we will send you instructions how to recover a password.</p>

        <div class="controls">
            <div class="main_input_box">
                <span class="add-on bg_lo"><i class="icon-envelope"></i></span>
                <input class="email required" autofocus type="text" id="email_addr_text" name="email_addr_text" placeholder="Email Address" />
            </div>
        </div>

        <div class="form-actions">
            <span class="pull-left"><a href="#" class="flip-link btn btn-success" id="to-login">&laquo; Back to login</a></span>
            <span class="pull-right"><button class="btn btn-info" type="submit">Recover</button></span>
        </div>
    </form>

</div>
