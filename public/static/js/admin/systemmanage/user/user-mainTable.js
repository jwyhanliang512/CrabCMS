var USERTGMHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;   //height
var USERTGM = document.querySelector('table');
var TGM = USERTGM.GM({
        supportRemind: true
        ,gridManagerName: 'test'
        ,isCombSorting: true
        ,height: (USERTGMHeight -200)+'px'
        ,supportAjaxPage:true
//		,supportSorting: true
        ,disableCache: true
        ,ajax_url: host_url + "/admin/systemmanage.user/get_user_list"
//                ,ajax_data : userList
        ,ajax_type: 'POST'
        ,query: {pluginId: 1}
        ,textAlign: 'center'
        ,pageSize:30
        ,columnData: [
                {
                        key: 'username',
                        width: '100px',
                        text: '用户名',
                        sorting: ''
                },{
                        key: 'companyname',
                        width: '110px',
                        text: '所属企业'
                },{
                        key: 'linkphone',
                        width: '110px',
                        text: '联系号码'
                },{
                        key: 'email',
                        width: '170px',
                        text: '邮箱'
                },{
                        key: 'lastlogintime',
                        width: '130px',
                        text: '最后登录时间',
                        sorting: ''
                },{
                        key: 'createman',
                        width: '90px',
                        text: '创建人'
                },{
                        key: 'createtime',
                        width: '130px',
                        text: '创建时间',
                        sorting: ''
                },{
                        key: 'modifyman',
                        width: '90px',
                        text: '修改人'
                },{
                        key: 'modifytime',
                        width: '130px',
                        text: '修改时间',
                        sorting: ''
                },{
                        key: 'action',
//				remind: 'the action',
                        width: '10%',
                        text: '操作',
                        template: function(action, rowObject){
                                return "<span class='plugin-action edit-action' learnLink-id='"+rowObject.objectid+"' onclick='addOrEditUser("+JSON.stringify(rowObject)+")'>编辑</span>"
                                                +"<span class='plugin-action edit-action' learnLink-id='"+rowObject.objectid+"' onclick='deleteUser("+JSON.stringify(rowObject)+")'>删除</span>";
                        }
                }
        ]
}, function(query){
        // 渲染完成后的回调函数
        var thList = $("table tr th");
        USERTGM.GM('hideTh', [thList[1], thList[9],thList[10]]); 
});

// 绑定搜索事件
document.querySelector('.search-action').addEventListener('click', function () {
        var _query = {
                username: document.querySelector('[name="username"]').value,
                linkphone: document.querySelector('[name="linkphone"]').value,
                companyids: document.querySelector('[name="companyid"]').value
        };
        USERTGM.GM('setQuery', _query, true);
});

// 绑定重置
document.querySelector('.reset-action').addEventListener('click', function () {
        document.querySelector('[name="username"]').value = '';
        document.querySelector('[name="linkphone"]').value = '';
        document.querySelector('[name="companyid"]').value = '';
        document.querySelector('[name="companyname"]').value = '';
        //重置企业树，取消勾选
        var zTree = $.fn.zTree.getZTreeObj("treeCompanyCheckbox");
        var checkNodes = zTree.getCheckedNodes(true);
        for (var i=0, l=checkNodes.length; i < l; i++) {
            zTree.checkNode(checkNodes[i], false, true);
        }
});