module.exports = {
  tableName: 'rhythm',
  autoPK : false,
  autoCreatedAt: false,
  autoUpdatedAt: false,
  attributes: {
    rhythm_id: {
      type: 'integer',
      unique: true,
      primaryKey: true,
      autoIncrement: true,
      columnName: 'rhythm_id',
    },
    user_id: {
      type: 'integer',
      columnName: 'user_id'
    },
    name: {
      type: 'string',
      columnName: 'name',
      size: 127
    },
  }
};
