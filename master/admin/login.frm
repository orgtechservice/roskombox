[__FORM__]
title = std:authorization

[login]
type=string
title=std:login
max-length = 30
min-length = 3

[password]
type=string
title=std:password
widget=password
min-length = 3
max-length = 16

[autologin]
type=boolean
title=std:autologin
default = off

[return]
type=string
widget=hidden

[ticket]
type=string
widget=hidden

[enter]
type=submit
title=std:enter
