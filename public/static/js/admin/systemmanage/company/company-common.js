function closeTip(type){
    if(type == 1){
        //隐藏时销毁bootstrapValidator验证,待下次弹出再重新加载验证
        $("#addEditCompanyForm").data('bootstrapValidator').destroy();
        $('#addEditCompanyForm').data('bootstrapValidator', null);
    }
    easyDialog.close();
}

function addOrEditCompany(data){

    //校验
    $('#addEditCompanyForm')
    .bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            companyname: {
                message: '企业名称验证失败',
                validators: {
                    notEmpty: {
                        message: '企业名称不能为空'
                    },
                    remote: {
                        url: host_url + "/admin/systemmanage.company/check_companyname_duplicate",
                        message: '企业名称已存在',
                        data: function() {
                            return {
                                companyname:$("#addEdit_companyname").val()
                            };
                        },
                        type: 'POST',
                        delay:500
                    },
                    callback: {
                        message: '请先选择所属企业',
                        callback: function (value, validator) {
                            var flag = true;
                            if($("#tree_company").val().length < 1){
                                $("#addEdit_username").val("");
                                flag = false;
                            }
                            return flag;
                        }
                    },
                    stringLength: {
                        min: 2,
                        max: 16,
                        message: '企业名称长度必须在2到16位之间'
                    }
                }
            },
            companycode: {
                message: '企业编码验证失败',
                validators: {
                    notEmpty: {
                        message: '企业编码不能为空'
                    },
                    remote: {
                        url: host_url + "/admin/systemmanage.company/check_companycode_duplicate",
                        message: '企业编码已存在',
                        data: function() {
                            return {
                                companycode:$("#addEdit_companycode").val()
                            };
                        },
                        type: 'POST',
                        delay:500
                    },
                    callback: {
                        message: '请先选择所属企业',
                        callback: function (value, validator) {
                            var flag = true;
                            if($("#tree_company").val().length < 1){
                                $("#addEdit_username").val("");
                                flag = false;
                            }
                            return flag;
                        }
                    },
                    stringLength: {
                        min: 5,
                        max: 16,
                        message: '企业编码长度必须在5到16位之间'
                    }
                }
            },
            pcompanyname: {
                message: '所属企业验证失败',
                validators: {
                    notEmpty: {
                        message: '所属企业不能为空'
                    }
                }
            },
            address: {
                message: '地址验证失败',
                validators: {
                    notEmpty: {
                        message: '地址不能为空'
                    }
                }
            },
            linkman: {
                validators: {
                    notEmpty: {
                        message: '联系人不能为空'
                    },
                    stringLength: {
                        min: 2,
                        max: 16,
                        message: '联系人长度必须在2到16位之间'
                    }
                }
            },
            linknumber: {
                validators: {
                    notEmpty: {
                        message: '联系号码不能为空'
                    },
                    stringLength: {
                        max: 20,
                        message: '联系号码不能超过20位'
                    }
                }
            }
        }
    });
    if(data == null){
        //弹出层
        easyDialog.open({
            container : 'addEditCompanyDiv',
            isOverlay : true
        });
        document.getElementById("addEditCompanytitle").innerHTML = "新增企业";
        document.getElementById("tree_company_div").style.display = "block"; //显示所属企业
        document.getElementById("addEdit_companyname").readOnly = false;
        document.getElementById("addEdit_objectid").value = "";
        document.getElementById("addEditCompanyForm").reset();//每次新增窗口弹出时，都清空表单并清除bootstrapValidator校验
        $('#addEditCompanyForm').data('bootstrapValidator').resetForm();
        //单独清除所属用户项
        var cityObj = $("#tree_company");
        cityObj.attr("value", "");
    }else{
        //编辑时去除企业名称校验
        $('#addEditCompanyForm').data('bootstrapValidator').removeField('companyname');
        $('#addEditCompanyForm').data('bootstrapValidator').removeField('companycode');
        //填充输入框
        document.getElementById("addEditCompanytitle").innerHTML = "编辑企业";
        document.getElementById("addEdit_companyname").value = data.companyname;
        document.getElementById("addEdit_companyname").readOnly = true;
        document.getElementById("addEdit_companycode").value = data.companycode;
        document.getElementById("addEdit_companycode").readOnly = true;
        document.getElementById("addEdit_linknumber").value = data.linknumber;
        document.getElementById("addEdit_linkman").value = data.linkman;
        document.getElementById("addEdit_address").value = data.address;
        document.getElementById("tree_company_div").style.display = "none";   //隐藏所属企业
        document.getElementById("addEdit_objectid").value = data.objectid;
        easyDialog.open({
            container : 'addEditCompanyDiv',
            isOverlay : true
        });
    }
}

function deleteCompany(data){
    easyDialog.open({
        container : 'deleteConfirmDiv',
        isOverlay : true
    });
    document.getElementById("deleteConfirmDiv").getElementsByTagName("p")[0].innerText = "删除企业【"+ data.companyname + "】?";
    document.getElementById("deleteConfirmDiv").getElementsByTagName("input")[0].value = data.objectid;
}

function confirmDeleteCompany(){
    $.ajax({
        type: 'POST',
        async : false, // 注意此处需要同步,锁住浏览器，其它操作必须等待请求完成才可以执行
        url: host_url + "/admin/systemmanage.company/delete_company",
        data: {
            objectid : document.getElementById("deleteConfirmDiv").getElementsByTagName("input")[0].value  //
        },
        success: function(data){
            window.location.reload();
        }
    });
}
