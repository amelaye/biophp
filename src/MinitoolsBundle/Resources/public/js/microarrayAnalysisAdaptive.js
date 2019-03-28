function getColorChannel(div) {
    $(div).each(function() {
        var red1 = 255;
        var blue1 = 255;
        var green1 = 255;
        var log1 = Number($(this).html());
        if(log1 < 0) {
            red1 = 255 - (-1) * Math.round(log1 * 255);
            if (red1 < 0) {
                red1 = 0;
            }
            green1 = red1;
        }
        else if (log1 > 0) {
            blue1 = 255 - Math.round(log1 * 255);
            if (blue1 < 0) {
                blue1 = 0;
            }
            green1 = blue1;
        }
        $(this).css("background-color", "rgb("+red1+", "+green1+", "+blue1+")");
    });
}