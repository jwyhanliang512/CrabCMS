var COMPANYTGMHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;   //height
var COMPANYTGM = document.querySelector('table');
var TGM = COMPANYTGM.GM({
        supportRemind: true
        ,gridManagerName: 'test'
        ,isCombSorting: true
        ,height: (COMPANYTGMHeight -200)+'px'
        ,supportAjaxPage:true
//		,supportSorting: true
        ,disableCache: true
        ,ajax_url: host_url + "/admin/systemmanage.company/get_company_list"
//                ,ajax_data : userList
        ,ajax_type: 'POST'
        ,query: {pluginId: 1}
        ,textAlign: 'center'
        ,pageSize:30
        ,columnData: [
                {
                        key: 'companyname',
                        width: '100px',
                        text: '企业名称',
                        sorting: ''
                },{
                        key: 'companycode',
                        width: '100px',
                        text: '企业编码',
                        sorting: ''
                },{
                        key: 'parentcompany',
                        width: '110px',
                        text: '所属企业'
                },{
                        key: 'address',
                        width: '110px',
                        text: '地址'
                },{
                        key: 'linkman',
                        width: '110px',
                        text: '联系人'
                },{
                        key: 'linknumber',
                        width: '110px',
                        text: '联系号码'
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
                                return "<span class='plugin-action edit-action' learnLink-id='"+rowObject.objectid+"' onclick='addOrEditCompany("+JSON.stringify(rowObject)+")'>编辑</span>"
                                                +"<span class='plugin-action edit-action' learnLink-id='"+rowObject.objectid+"' onclick='deleteCompany("+JSON.stringify(rowObject)+")'>删除</span>";
                        }
                }
        ]
}, function(query){
        // 渲染完成后的回调函数
        var thList = $("table tr th");
        COMPANYTGM.GM('hideTh', [thList[1], thList[10], thList[11]]); 
});

// 绑定搜索事件
document.querySelector('.search-action').addEventListener('click', function () {
        var _query = {
                companyname: document.querySelector('[name="companyname"]').value,
                address: document.querySelector('[name="address"]').value,
                companyids: document.querySelector('[name="companyid"]').value
        };
        COMPANYTGM.GM('setQuery', _query, true);
});

// 绑定重置
document.querySelector('.reset-action').addEventListener('click', function () {
        document.querySelector('[name="companyname"]').value = '';
        document.querySelector('[name="address"]').value = '';
        document.querySelector('[name="companyid"]').value = '';
        document.querySelector('[name="checkcompanyname"]').value = '';
        //重置企业树，取消勾选
        var zTree = $.fn.zTree.getZTreeObj("treeCompanyCheckbox");
        var checkNodes = zTree.getCheckedNodes(true);
        for (var i=0, l=checkNodes.length; i < l; i++) {
            zTree.checkNode(checkNodes[i], false, true);
        }
});