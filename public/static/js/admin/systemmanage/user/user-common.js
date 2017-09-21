function closeTip(type){
    if(type == 1){
        //隐藏时销毁bootstrapValidator验证,待下次弹出再重新加载验证
        $("#addEditUserForm").data('bootstrapValidator').destroy();
        $('#addEditUserForm').data('bootstrapValidator', null);
    }
    easyDialog.close();
}

function addOrEditUser(data){
    if(data == null){
        //校验
        $('#addEditUserForm')
        .bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                username: {
                    message: '用户名验证失败',
                    validators: {
                        notEmpty: {
                            message: '用户名不能为空'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9_]+$/,
                            message: '用户名只能包含大写、小写字母，数字和下划线'
                        },
                        remote: {
                            url: host_url + "/admin/systemmanage.user/check_user_duplicate",
                            message: '用户名已存在',
                            data: function() {
                                return {
                                    username: $("#addEdit_username").val()
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
                            min: 6,
                            max: 16,
                            message: '用户名长度必须在6到16位之间'
                        }
                    }
                },
                companyname: {
                    message: '所属企业验证失败',
                    validators: {
                        notEmpty: {
                            message: '所属企业不能为空'
                        }
                    }
                },
                password: {
                    validators: {
                        notEmpty: {
                            message: '密码不能为空'
                        },
                        stringLength: {
                            min: 6,
                            max: 18,
                            message: '密码必须在6到18位之间'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9]+$/,
                            message: '密码只能包含大写、小写字母，数字'
                        },
                        identical: {
                            field: 'confirmPassword',
                            message: '两次密码输入不一致'
                        },
                        different: {
                            field: 'username',
                            message: '密码不能为用户名'
                        }
                    }
                },
                confirmPassword: {
                    validators: {
                        notEmpty: {
                            message: '确认密码不能为空'
                        },
                        identical: {
                            field: 'password',
                            message: '两次密码输入不一致'
                        },
                        different: {
                            field: 'username',
                            message: '密码不能为用户名'
                        }
                    }
                },
                linkphone: {
                    validators: {
                        notEmpty: {
                            message: '联系号码不能为空'
                        }
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: '邮箱不能为空'
                        },
                        emailAddress: {
                            message: '邮箱地址格式有误'
                        }
                    }
                }
            }
        });
        //弹出层
        easyDialog.open({
            container : 'addEditUserDiv',
            isOverlay : true
        });
        document.getElementById("addEditUsertitle").innerHTML = "新增用户";
        document.getElementById("addEdit_username").readOnly = false;
        document.getElementById("confirmPasswordDiv").style.display = "block"; //显示确认密码div
        document.getElementById("addEditUserForm").reset();//每次新增窗口弹出时，都清空表单并清除bootstrapValidator校验
        $('#addEditUserForm').data('bootstrapValidator').resetForm();
        //单独清除所属用户项
        var cityObj = $("#tree_company");
        cityObj.attr("value", "");
    }else{
        //校验
        $('#addEditUserForm')
        .bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                password: {
                    validators: {
                        notEmpty: {
                            message: '密码不能为空'
                        },
                        stringLength: {
                            min: 6,
                            max: 18,
                            message: '密码必须在6到18位之间'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9]+$/,
                            message: '密码只能包含大写、小写字母，数字'
                        },
                        different: {
                            field: 'username',
                            message: '密码不能为用户名'
                        }
                    }
                },
                linkphone: {
                    validators: {
                        notEmpty: {
                            message: '联系号码不能为空'
                        }
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: '邮箱不能为空'
                        },
                        emailAddress: {
                            message: '邮箱地址格式有误'
                        }
                    }
                }
            }
        });
        //填充输入框
        document.getElementById("addEditUsertitle").innerHTML = "编辑用户";
        document.getElementById("confirmPasswordDiv").style.display = "none";   //隐藏确认密码div
        document.getElementById("addEdit_confirmPassword").value = "";//置空确认密码输入框，并以此为后台判断编辑与新增的依据
        document.getElementById("addEdit_username").value = data.username;
        document.getElementById("addEdit_username").readOnly = true;
        document.getElementById("addEdit_password").value = data.password;
        document.getElementById("addEdit_linkphone").value = data.linkphone;
        document.getElementById("addEdit_email").value = data.email;
        $("#tree_company").attr("value", data.companyname);
        document.getElementById("tree_companyid").value = data.companyid;
        document.getElementById("addEdit_objectid").value = data.objectid;
        easyDialog.open({
            container : 'addEditUserDiv',
            isOverlay : true
        });
    }
}

function deleteUser(data){
    easyDialog.open({
        container : 'deleteConfirmDiv',
        isOverlay : true
    });
    document.getElementById("deleteConfirmDiv").getElementsByTagName("p")[0].innerText = "删除用户【"+ data.username + "】?";
    document.getElementById("deleteConfirmDiv").getElementsByTagName("input")[0].value = data.objectid;
}

function confirmDeleteUser(){
    console.log(document.getElementById("deleteConfirmDiv").getElementsByTagName("input")[0].value);
    $.ajax({
        type: 'POST',
        async : false, // 注意此处需要同步,锁住浏览器，其它操作必须等待请求完成才可以执行
        url: host_url + "/admin/systemmanage.user/delete_user",
        data: {
            objectid : document.getElementById("deleteConfirmDiv").getElementsByTagName("input")[0].value  //
        },
        success: function(data){
            window.location.reload();
        }
    });
}