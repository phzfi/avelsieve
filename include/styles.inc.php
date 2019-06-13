<?php

function avelsieve_css_styles() {
    global $color;
    return '
a:hover {
        text-decoration: none;
        background: '.$color[3].';
}

.avelsieve_div {
        width: 90%;
        margin-left: auto;
        padding: 0.5em;
        margin-right: auto;
        text-align:left;
        border: 3px solid '.$color[5].';
}
.avelsieve_rule_disabled {
        font-size: 0.7em;
        background-color: inherit;
        color:'.$color[15].';
}
.avelsieve_quoted {
        border-left: 1em solid '.$color[12].';
}
.avelsieve_source {
        width: 99%;
        overflow:auto;
        border: 1px dotted '.$color[12].';
        font-family: monospace;
        font-size: 0.8em;
}
.avelsieve_expand_link {
        color: '.$color[7].';
        text-decoration: none;
        cursor: pointer;
}
.avelsieve_more_options_link {
        color: '.$color[7].';
        text-decoration: underline;
        font-size: 0.9em;
}
';
}


