<script type="text/javascript">
// <![CDATA[

function verifyUsername(username)
{
	if(username != '')
	{
		if(username.length < 2)
		{
			writediv('pseudobox', '<span class="gensmall" style="color: #dd3333;"><strong>' + username + ' :<\/strong>{L_UN_SHORT}<\/span>');
		}
		else if(username.length > 36)
		{
			writediv('pseudobox', '<span class="gensmall" style="color: #dd3333;"><strong>' + username + ' :<\/strong>{L_UN_LONG}<\/span>');
		}
		else if(dest_string = file_request('{U_AJAX_VERIFY}?mode=username&verify=' + escape(username)))
		{
			if(dest_string == 1)
			{
				writediv('pseudobox', '<span class="gensmall" style="color: #dd3333;"><strong>' + username + ' :<\/strong>{L_UN_TAKEN}<\/span>');
			}
			else if(dest_string == 2)
			{
				writediv('pseudobox', '<span class="gensmall" style="color: #228822;"><strong>' + username + ' :<\/strong>{L_UN_FREE}<\/span>');
			}
			else
			{
				writediv('pseudobox', dest_string);
			}
		}
	}
}

function verifyPWD(password)
{
	if(password != '')
	{
		if(password.length < 2)
		{
			writediv('pwdbox', '<span class="gensmall" style="color: #dd3333;"><strong>' + password + ' :<\/strong>{L_PWD_SHORT}<\/span>');
		}
		else if(dest_string = file_request('{U_AJAX_VERIFY}?mode=password&verify=' + escape(password)))
		{
			if(dest_string == 1)
			{
				writediv('pwdbox', '<span class="gensmall" style="color: #dd3333;"><strong>' + password + ' :<\/strong>{L_PWD_EASY}<\/span>');
			}
			else if(dest_string == 2)
			{
				writediv('pwdbox', '<span class="gensmall" style="color: #228822;"><strong>' + password + ' :<\/strong>{L_PWD_OK}<\/span>');
			}
			else
			{
				writediv('pwdbox', dest_string);
			}
		}
	}
}

function verifyEmail(emailaddress)
{
	if(emailaddress != '')
	{
		if(emailaddress.length < 2)
		{
			writediv('emailbox', '<span class="gensmall" style="color: #dd3333;"><strong>' + emailaddress + ' :<\/strong>{L_EMAIL_INVALID}<\/span>');
		}
		else if(dest_string = file_request('{U_AJAX_VERIFY}?mode=email&verify=' + escape(emailaddress)))
		{
			if(dest_string == 1)
			{
				writediv('emailbox', '<span class="gensmall" style="color: #dd3333;"><strong>' + emailaddress + ' :<\/strong>{L_EMAIL_INVALID}<\/span>');
			}
			else if(dest_string == 2)
			{
				writediv('emailbox', '<span class="gensmall" style="color: #228822;"><strong>' + emailaddress + ' :<\/strong>{L_EMAIL_OK}<\/span>');
			}
			else
			{
				writediv('emailbox', dest_string);
			}
		}
	}
}

// ]]>
</script>
