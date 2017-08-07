

function beforeClickTypecodeTree(treeId, treeNode) {
//        var check = (treeNode && !treeNode.isParent);
//        if (!check) alert("只能选择城市...");
//        return check;
}

function onClickTypecodeTree(e, treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeTypecode"),
        nodes = zTree.getSelectedNodes(),
        v = "";
        cid = "";
        nodes.sort(function compare(a,b){return a.id-b.id;});
        for (var i=0, l=nodes.length; i<l; i++) {
                v += nodes[i].name + ",";
                cid += nodes[i].id;
        }
        if (v.length > 0 ) v = v.substring(0, v.length-1);
        var cityObj = $("#tree_typecode");
        cityObj.attr("value", v);
        var cityObj = $("#tree_typecodeid");
        cityObj.attr("value", cid);
        hideMenuTypecodeTree();
}

function showMenuTypecodeTree() {
        var cityObj = $("#tree_typecode");
        var cityOffset = $("#tree_typecode").offset();
        $("#menuTypecode").css({left:cityOffset.left + "px", top:cityOffset.top + cityObj.outerHeight() + "px","z-index":"4000","background-color":"white","overflow":"scroll"}).slideDown("fast");

        $("body").bind("mousedown", onBodyDownTypecodeTree);
}
function hideMenuTypecodeTree() {
        $("#menuTypecode").fadeOut("fast");
        $("body").unbind("mousedown", onBodyDownTypecodeTree);
}
function onBodyDownTypecodeTree(event) {
        if (!(event.target.id == "menuBtn" || event.target.id == "menuTypecode" || $(event.target).parents("#menuTypecode").length>0)) {
                hideMenuTypecodeTree();
        }
}